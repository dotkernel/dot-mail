<?php

declare(strict_types=1);

namespace Dot\Mail\Service;

use Dot\Mail\Email;
use Dot\Mail\Result\ResultInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Part\AbstractPart;

interface MailServiceInterface
{
    public const DEFAULT_CHARSET = 'utf-8';

    public function send(): ResultInterface;

    public function getMessage(): Email;

    public function getTransport(): TransportInterface;

    /**
     * Sets the body: a string is used as HTML in the given charset (utf-8 by default),
     * a MIME part is used as it is and the charset is ignored.
     */
    public function setBody(string|AbstractPart $body, ?string $charset = null): void;

    public function setSubject(string $subject): void;

    public function addAttachment(string $path, ?string $filename = null): void;

    /**
     * @param array<int|string, string> $paths file paths, optionally keyed by attachment name
     */
    public function addAttachments(array $paths): void;

    /**
     * @return array<int|string, string>
     */
    public function getAttachments(): array;

    /**
     * @param array<int|string, string> $paths file paths, optionally keyed by attachment name
     */
    public function setAttachments(array $paths): void;

    public function setTransport(TransportInterface $transport): void;

    /**
     * Restores the message and attachments to the configured defaults.
     * Called automatically after every send() attempt.
     */
    public function reset(): void;
}
