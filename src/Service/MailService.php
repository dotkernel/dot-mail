<?php

declare(strict_types=1);

namespace Dot\Mail\Service;

use Dot\Mail\Email;
use Dot\Mail\Event\MailEvent;
use Dot\Mail\Event\MailEventListenerAwareInterface;
use Dot\Mail\Event\MailEventListenerAwareTrait;
use Dot\Mail\Exception\MailException;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Result\MailResult;
use Dot\Mail\Result\ResultInterface;
use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;

use function array_merge;
use function basename;
use function count;
use function fopen;
use function is_file;
use function is_string;

class MailService implements MailServiceInterface, MailEventListenerAwareInterface
{
    use MailEventListenerAwareTrait;

    protected LogServiceInterface $logService;
    protected Email $message;
    protected TransportInterface $transport;
    protected MailOptions $mailOptions;
    protected array $attachments = [];

    public function __construct(
        LogServiceInterface $logService,
        Email $message,
        TransportInterface $transport,
        MailOptions $mailOptions
    ) {
        $this->logService  = $logService;
        $this->message     = $message;
        $this->transport   = $transport;
        $this->mailOptions = $mailOptions;
    }

    /**
     * @throws MailException|TransportExceptionInterface
     */
    public function send(): ResultInterface
    {
        $result = new MailResult();

        $this->message->setEncoding('utf-8');
        try {
            $this->getEventManager()->triggerEvent($this->createMailEvent());

            //attach files before sending
            $this->attachFiles();
            $this->getTransport()->send($this->getMessage());
            $this->getMessage()->setBody(null);

            $this->getEventManager()->triggerEvent($this->createMailEvent(MailEvent::EVENT_MAIL_POST_SEND, $result));
        } catch (Exception $e) {
            $result = $this->createMailResultFromException($e);
            //trigger error event
            $this->getEventManager()->triggerEvent($this->createMailEvent(MailEvent::EVENT_MAIL_SEND_ERROR, $result));
            throw new MailException($result->getMessage());
        }

        if ($result->isValid()) {
            $this->logService->sent($this->getMessage());
        }

        return $result;
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

    public function attachFiles(): false|Email
    {
        if (count($this->attachments) === 0) {
            return false;
        }

        $mimeMessage = $this->message->getBody();

        //generate a new Part for each attachment
        foreach ($this->attachments as $key => $attachment) {
            if (! is_file($attachment)) {
                continue;
            }
            $basename     = is_string($key) ? $key : basename($attachment);
            $attachedFile = new DataPart(fopen($attachment, 'r'), $basename);
            $mimeMessage  = new MixedPart($mimeMessage, $attachedFile);

            $this->message->setBody($mimeMessage);
        }

        return $this->message;
    }

    public function setBody(string|AbstractPart $body, ?string $charset = null): void
    {
        if (is_string($body)) {
            $this->message->html($body);
        } else {
            $this->message->setBody($body);
        }
    }

    public function createMailResultFromException(Exception $e): ResultInterface
    {
        return new MailResult(false, $e->getMessage(), $e);
    }

    public function getMessage(): Email
    {
        return $this->message;
    }

    public function setSubject(string $subject): void
    {
        $this->message->subject($subject);
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
}
