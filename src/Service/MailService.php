<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Service;

use Dot\Mail\Event\MailEvent;
use Dot\Mail\Event\MailEventListenerAwareInterface;
use Dot\Mail\Event\MailEventListenerAwareTrait;
use Dot\Mail\Exception\InvalidArgumentException;
use Dot\Mail\Exception\MailException;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Result\MailResult;
use Dot\Mail\Result\ResultInterface;
use Laminas\Mail\Message;
use Laminas\Mail\Storage\Imap;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;

/**
 * Class MailService
 * @package Dot\Mail\Service
 */
class MailService implements
    MailServiceInterface,
    MailEventListenerAwareInterface
{
    use MailEventListenerAwareTrait;

    protected LogServiceInterface $logService;

    protected Message $message;

    protected TransportInterface $transport;

    protected array $attachments = [];

    /**
     * MailService constructor.
     * @param LogServiceInterface $logService
     * @param Message $message
     * @param TransportInterface $transport
     */
    public function __construct(
        LogServiceInterface $logService,
        Message $message,
        TransportInterface $transport,
        Imap $storage,
        MailOptions $mailOptions
    ) {
        $this->logService = $logService;
        $this->message = $message;
        $this->transport = $transport;
        $this->storage = $storage;
        $this->mailOptions = $mailOptions;
    }

    /**
     * @return ResultInterface
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
        } catch (\Exception $e) {
            $result = $this->createMailResultFromException($e);
            //trigger error event
            $this->getEventManager()->triggerEvent($this->createMailEvent(MailEvent::EVENT_MAIL_SEND_ERROR, $result));
        }

        if ($result->isValid()) {
            $this->logService->sent($this->message);
        }

        //save copy of sent message to folders
        if ($this->mailOptions->getSaveSentMessageFolder()) {
            foreach ($this->mailOptions->getSaveSentMessageFolder() as $folder) {
                $this->storage->appendMessage($this->message->toString(), $folder);
            }
        }

        return $result;
    }

    /**
     * @param string $name
     * @param ResultInterface|null $result
     * @return MailEvent
     */
    protected function createMailEvent(
        $name = MailEvent::EVENT_MAIL_PRE_SEND,
        ResultInterface $result = null
    ): MailEvent {
        $event = new MailEvent($this, $name);
        if (isset($result)) {
            $event->setResult($result);
        }
        return $event;
    }

    /**
     * Attach the files to the message before sending it
     */
    protected function attachFiles()
    {
        if (count($this->attachments) === 0) {
            return;
        }

        $mimeMessage = $this->message->getBody();
        if (is_string($mimeMessage)) {
            $originalBodyPart = new MimePart($mimeMessage);
            $originalBodyPart->type = $mimeMessage != strip_tags($mimeMessage)
                ? Mime::TYPE_HTML
                : Mime::TYPE_TEXT;

            $this->setBody($originalBodyPart);
            $mimeMessage = $this->message->getBody();
        }

        $oldParts = $mimeMessage->getParts();

        //generate a new Part for each attachment
        $attachmentParts = [];
        $info = new \finfo(FILEINFO_MIME_TYPE);
        foreach ($this->attachments as $key => $attachment) {
            if (!is_file($attachment)) {
                continue;
            }

            $basename = is_string($key) ? $key : basename($attachment);

            $part = new MimePart(fopen($attachment, 'r'));
            $part->id = $basename;
            $part->filename = $basename;
            $part->type = $info->file($attachment);
            $part->encoding = Mime::ENCODING_BASE64;
            $part->disposition = Mime::DISPOSITION_ATTACHMENT;
            $attachmentParts[] = $part;
        }

        $body = new MimeMessage();
        $body->setParts(array_merge($oldParts, $attachmentParts));
        $this->message->setBody($body);
    }

    /**
     * @param mixed $body
     * @param string|null $charset
     */
    public function setBody($body, string $charset = null)
    {
        if (is_string($body)) {
            //create a mime\part and wrap it into a mime\message
            $mimePart = new MimePart($body);
            $mimePart->type = $body != strip_tags($body) ? Mime::TYPE_HTML : Mime::TYPE_TEXT;
            $mimePart->charset = $charset ?: self::DEFAULT_CHARSET;
            $body = new MimeMessage();
            $body->setParts([$mimePart]);
        } elseif ($body instanceof MimePart) {
            if (isset($charset)) {
                $body->charset = $charset;
            }

            $mimeMessage = new MimeMessage();
            $mimeMessage->setParts([$body]);
            $body = $mimeMessage;
        }

        //if the body is not a string or mime message at this point, it is not a valid argument
        if (!is_string($body) && !$body instanceof MimeMessage) {
            throw new InvalidArgumentException(sprintf(
                'Provided body is not valid. It should be one of "%s". %s provided',
                implode('", "', ['string', 'Laminas\Mime\Part', 'Laminas\Mime\Message']),
                is_object($body) ? get_class($body) : gettype($body)
            ));
        }

        // The headers Content-type and Content-transfer-encoding are duplicated every time the body is set.
        // Removing them before setting the body prevents this error
        $this->message->getHeaders()->removeHeader('content-type');
        $this->message->getHeaders()->removeHeader('content-transfer-encoding');
        $this->message->setBody($body);
    }

    /**
     * @param \Exception $e
     * @return ResultInterface
     */
    protected function createMailResultFromException(\Exception $e): ResultInterface
    {
        return new MailResult(false, $e->getMessage(), $e);
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->message->setSubject($subject);
    }

    /**
     * @param string $path
     * @param string $filename
     */
    public function addAttachment(string $path, string $filename = null)
    {
        if (isset($filename)) {
            $this->attachments[$filename] = $path;
        } else {
            $this->attachments[] = $path;
        }
    }

    /**
     * @param array $paths
     */
    public function addAttachments(array $paths)
    {
        $this->setAttachments(array_merge($this->attachments, $paths));
    }

    /**
     * @return array
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param array $paths
     */
    public function setAttachments(array $paths)
    {
        $this->attachments = $paths;
    }

    /**
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    /**
     * @param TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @return array
     */
    public function getFolderGlobalNames()
    {
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
