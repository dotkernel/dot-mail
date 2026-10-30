<?php

declare(strict_types=1);

namespace Dot\Mail\Service;

use DirectoryIterator;
use Dot\Mail\Email;
use Dot\Mail\Event\MailEvent;
use Dot\Mail\Event\MailEventListenerAwareInterface;
use Dot\Mail\Event\MailEventListenerAwareTrait;
use Dot\Mail\Exception\MailException;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Result\MailResult;
use Dot\Mail\Result\ResultInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Throwable;

use function array_merge;
use function basename;
use function count;
use function is_dir;
use function is_file;
use function is_readable;
use function is_string;
use function sprintf;
use function str_starts_with;

class MailService implements MailServiceInterface, MailEventListenerAwareInterface
{
    use MailEventListenerAwareTrait;

    protected LogServiceInterface $logService;
    protected Email $message;
    protected Email $prototype;
    protected TransportInterface $transport;
    protected MailOptions $mailOptions;
    protected array $attachments        = [];
    protected array $defaultAttachments = [];

    /**
     * @param array $attachments The default attachments, restored by reset() after every send
     */
    public function __construct(
        LogServiceInterface $logService,
        Email $message,
        TransportInterface $transport,
        MailOptions $mailOptions,
        array $attachments = []
    ) {
        $this->logService         = $logService;
        $this->message            = $message;
        $this->prototype          = clone $message;
        $this->transport          = $transport;
        $this->mailOptions        = $mailOptions;
        $this->attachments        = $attachments;
        $this->defaultAttachments = $attachments;
    }

    /**
     * Sends the current message, then restores the configured defaults (see reset()).
     * Recipients, subject, body and attachments must be set again for every message.
     * An exception thrown by a post-send listener propagates unchanged: the message was already delivered.
     *
     * @throws MailException
     */
    public function send(): ResultInterface
    {
        $result = new MailResult();

        if ($this->message->getEncoding() === null) {
            $this->message->setEncoding(self::DEFAULT_CHARSET);
        }

        try {
            $this->getEventManager()->triggerEvent($this->createMailEvent());

            //attach files before sending
            $this->attachFiles();
            $this->getTransport()->send($this->getMessage());
        } catch (Throwable $e) {
            $result = $this->createMailResultFromException($e);
            try {
                //trigger error event
                $this->getEventManager()->triggerEvent(
                    $this->createMailEvent(MailEvent::EVENT_MAIL_SEND_ERROR, $result)
                );
            } finally {
                $this->reset();
            }
            throw new MailException($result->getMessage(), (int) $e->getCode(), $e);
        }

        try {
            $this->logSent();
            $this->getEventManager()->triggerEvent($this->createMailEvent(MailEvent::EVENT_MAIL_POST_SEND, $result));
        } finally {
            $this->reset();
        }

        return $result;
    }

    public function reset(): void
    {
        $this->message     = clone $this->prototype;
        $this->attachments = $this->defaultAttachments;
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

    /**
     * @throws MailException When an attachment does not exist or is not readable.
     */
    public function attachFiles(): false|Email
    {
        $attachments = array_merge($this->attachments, $this->getDirectoryAttachments());
        if (count($attachments) === 0) {
            return false;
        }

        $body  = $this->message->getBody();
        $parts = [];

        foreach ($attachments as $key => $attachment) {
            if (! is_file($attachment) || ! is_readable($attachment)) {
                throw new MailException(
                    sprintf('Attachment "%s" does not exist or is not readable', $attachment)
                );
            }
            $basename = is_string($key) ? $key : basename($attachment);
            $parts[]  = DataPart::fromPath($attachment, $basename);
        }

        $this->message->setBody(new MixedPart($body, ...$parts));

        return $this->message;
    }

    public function setBody(string|AbstractPart $body, ?string $charset = null): void
    {
        if (is_string($body)) {
            $this->message->html($body, $charset ?? self::DEFAULT_CHARSET);
        } else {
            $this->message->setBody($body);
        }
    }

    public function createMailResultFromException(Throwable $e): ResultInterface
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

    /**
     * A failed log write must never turn a delivered email into an error
     */
    protected function logSent(): void
    {
        try {
            $this->logService->sent($this->getMessage());
        } catch (Throwable) {
            // intentionally ignored
        }
    }

    /**
     * Collects the files of the configured attachments directory at send time.
     * Relative paths are resolved against the current working directory; dotfiles are skipped.
     *
     * @return string[]
     */
    protected function getDirectoryAttachments(): array
    {
        $dir = $this->mailOptions->getMessageOptions()->getAttachments()->getDir();

        if (($dir['iterate'] ?? false) !== true || ! is_string($dir['path'] ?? null) || ! is_dir($dir['path'])) {
            return [];
        }

        $files = ($dir['recursive'] ?? false) === true
            ? new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir['path'], FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            )
            : new DirectoryIterator($dir['path']);

        $paths = [];
        foreach ($files as $fileInfo) {
            if ($fileInfo->isDir() || str_starts_with($fileInfo->getFilename(), '.')) {
                continue;
            }

            $paths[] = $fileInfo->getPathname();
        }

        return $paths;
    }
}
