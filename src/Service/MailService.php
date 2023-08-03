<?php

declare(strict_types=1);

namespace Dot\Mail\Service;

use Dot\Mail\Event\MailEvent;
use Dot\Mail\Event\MailEventListenerAwareInterface;
use Dot\Mail\Event\MailEventListenerAwareTrait;
use Dot\Mail\Exception\MailException;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Result\MailResult;
use Dot\Mail\Result\ResultInterface;
use Exception;
use finfo;
use Laminas\Mail\Message;
use Laminas\Mail\Storage\Imap;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;

use function array_merge;
use function basename;
use function count;
use function fopen;
use function is_file;
use function is_string;
use function strip_tags;

use const FILEINFO_MIME_TYPE;

class MailService implements MailServiceInterface, MailEventListenerAwareInterface
{
    use MailEventListenerAwareTrait;

    protected LogServiceInterface $logService;
    protected Message $message;
    protected TransportInterface $transport;
    protected MailOptions $mailOptions;
    protected array $attachments = [];
    protected ?Imap $storage;

    public function __construct(
        LogServiceInterface $logService,
        Message $message,
        TransportInterface $transport,
        MailOptions $mailOptions
    ) {
        $this->logService  = $logService;
        $this->message     = $message;
        $this->transport   = $transport;
        $this->mailOptions = $mailOptions;
    }

    /**
     * @throws MailException
     */
    public function send(): ResultInterface
    {
        $result = new MailResult();
        /*
         * Enforce the UTF-8 encoding to message body
         * @see https://github.com/dotkernel/dot-mail/issues/9
        */
        $this->message->setEncoding('utf-8');
        try {
            $this->getEventManager()->triggerEvent($this->createMailEvent());

            //attach files before sending
            $this->attachFiles();

            $this->transport->send($this->message);

            $this->getEventManager()->triggerEvent($this->createMailEvent(MailEvent::EVENT_MAIL_POST_SEND, $result));
        } catch (Exception $e) {
            $result = $this->createMailResultFromException($e);
            //trigger error event
            $this->getEventManager()->triggerEvent($this->createMailEvent(MailEvent::EVENT_MAIL_SEND_ERROR, $result));
            throw new MailException($result->getMessage());
        }

        if ($result->isValid()) {
            $this->logService->sent($this->message);
        }

        //save copy of sent message to folders
        if (
            $this->mailOptions->getTransport() === Smtp::class
            && $this->mailOptions->getSaveSentMessageFolder()
        ) {
            $this->storage = $this->createStorage();
            if ($this->storage) {
                foreach ($this->mailOptions->getSaveSentMessageFolder() as $folder) {
                    $this->storage->appendMessage($this->message->toString(), $folder);
                }
            }
        }

        return $result;
    }

    public function createStorage(): ?Imap
    {
        $host = $this->mailOptions->getSmtpOptions()->getHost();
        if (empty($host)) {
            return null;
        }
        $connectionConfig = $this->mailOptions->getSmtpOptions()->getConnectionConfig();

        if (empty($connectionConfig['username']) || empty($connectionConfig['password'])) {
            return null;
        }

        return new Imap([
            'host'     => $host,
            'user'     => $connectionConfig['username'],
            'password' => $connectionConfig['password'],
        ]);
    }

    public function createMailEvent(
        string $name = MailEvent::EVENT_MAIL_PRE_SEND,
        ?ResultInterface $result = null
    ): MailEvent {
        $event = new MailEvent($this, $name);
        if (isset($result)) {
            $event->setResult($result);
        }
        return $event;
    }

    public function attachFiles(): false|Message
    {
        if (count($this->attachments) === 0) {
            return false;
        }

        $mimeMessage = $this->message->getBody();

        if (is_string($mimeMessage)) {
            $originalBodyPart       = new MimePart($mimeMessage);
            $originalBodyPart->type = $mimeMessage !== strip_tags($mimeMessage)
                ? Mime::TYPE_HTML
                : Mime::TYPE_TEXT;

            $this->setBody($originalBodyPart);
            $mimeMessage = $this->message->getBody();
        }

        $oldParts = $mimeMessage->getParts();

        //generate a new Part for each attachment
        $attachmentParts = [];
        $info            = new finfo(FILEINFO_MIME_TYPE);

        foreach ($this->attachments as $key => $attachment) {
            if (! is_file($attachment)) {
                continue;
            }
            $basename          = is_string($key) ? $key : basename($attachment);
            $part              = new MimePart(fopen($attachment, 'r'));
            $part->id          = $basename;
            $part->filename    = $basename;
            $part->type        = $info->file($attachment);
            $part->encoding    = Mime::ENCODING_BASE64;
            $part->disposition = Mime::DISPOSITION_ATTACHMENT;
            $attachmentParts[] = $part;
        }
        $body = new MimeMessage();
        $body->setParts(array_merge($oldParts, $attachmentParts));

        return $this->message->setBody($body);
    }

    public function setBody(string|MimePart $body, ?string $charset = null): void
    {
        if (is_string($body)) {
            //create a mime\part and wrap it into a mime\message
            $mimePart          = new MimePart($body);
            $mimePart->type    = $body !== strip_tags($body) ? Mime::TYPE_HTML : Mime::TYPE_TEXT;
            $mimePart->charset = $charset ?: self::DEFAULT_CHARSET;
            $body              = new MimeMessage();
            $body->setParts([$mimePart]);
        } else {
            if (isset($charset)) {
                $body->charset = $charset;
            }

            $mimeMessage = new MimeMessage();
            $mimeMessage->setParts([$body]);
            $body = $mimeMessage;
        }

        // The headers Content-type and Content-transfer-encoding are duplicated every time the body is set.
        // Removing them before setting the body prevents this error
        $this->message->getHeaders()->removeHeader('content-type');
        $this->message->getHeaders()->removeHeader('content-transfer-encoding');
        $this->message->setBody($body);
    }

    public function createMailResultFromException(Exception $e): ResultInterface
    {
        return new MailResult(false, $e->getMessage(), $e);
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function setSubject(string $subject): void
    {
        $this->message->setSubject($subject);
    }

    public function addAttachment(string $path, ?string $filename = null): void
    {
        if (isset($filename)) {
            $this->attachments[$filename] = $path;
        } else {
            $this->attachments[] = $path;
        }
    }

    public function addAttachments(array $paths): void
    {
        $this->setAttachments(array_merge($this->attachments, $paths));
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function setAttachments(array $paths): void
    {
        $this->attachments = $paths;
    }

    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function getStorage(): ?Imap
    {
        return $this->storage;
    }

    public function setStorage(?Imap $storage): void
    {
        $this->storage = $storage;
    }

    public function getFolderGlobalNames(): array|false
    {
        $this->storage = $this->createStorage();
        if (! $this->storage) {
            return false;
        }
        $folderGlobalNames = [];
        foreach ($this->storage->getFolders() as $folder) {
            $folderGlobalNames[] = $folder->getGlobalName();
        }
        foreach ($this->storage->getFolders()->getChildren() as $folder) {
            $folderGlobalNames[] = $folder->getGlobalName();
        }
        return $folderGlobalNames;
    }
}
