<?php

declare(strict_types=1);

namespace Dot\Mail\Service;

use Dot\Mail\Result\ResultInterface;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime\Part as MimePart;

interface MailServiceInterface
{
    public const DEFAULT_CHARSET = 'utf-8';

    public function send(): ResultInterface;

    public function getMessage(): Message;

    public function getTransport(): TransportInterface;

    public function setBody(string|MimePart $body, ?string $charset = null): void;

    public function setSubject(string $subject): void;

    public function addAttachment(string $path, ?string $filename = null): void;

    public function addAttachments(array $paths): void;

    public function getAttachments(): array;

    public function setAttachments(array $paths): void;

    public function setTransport(TransportInterface $transport): void;

    public function getFolderGlobalNames(): array|false;
}
