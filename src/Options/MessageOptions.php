<?php

declare(strict_types=1);

namespace Dot\Mail\Options;

use Laminas\Stdlib\AbstractOptions;

/**
 * @template TValue
 * @template-extends AbstractOptions<TValue>
 */
class MessageOptions extends AbstractOptions
{
    protected string $from        = '';
    protected string $fromName    = '';
    protected string $replyTo     = '';
    protected string $replyToName = '';
    protected array $to           = [];
    protected array $cc           = [];
    protected array $bcc          = [];
    protected string $subject     = '';
    protected BodyOptions $body;
    protected AttachmentsOptions $attachments;

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): void
    {
        $this->fromName = $fromName;
    }

    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    public function setReplyTo(string $replyTo): void
    {
        $this->replyTo = $replyTo;
    }

    public function getReplyToName(): string
    {
        return $this->replyToName;
    }

    public function setReplyToName(string $replyToName): void
    {
        $this->replyToName = $replyToName;
    }

    public function getTo(): array
    {
        return $this->to;
    }

    public function setTo(array $to): void
    {
        $this->to = $to;
    }

    public function getCc(): array
    {
        return $this->cc;
    }

    public function setCc(array $cc): void
    {
        $this->cc = $cc;
    }

    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function setBcc(array $bcc): void
    {
        $this->bcc = $bcc;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getBody(): BodyOptions
    {
        return $this->body;
    }

    public function setBody(array $body): void
    {
        $this->body = new BodyOptions($body);
    }

    public function getAttachments(): AttachmentsOptions
    {
        return $this->attachments;
    }

    public function setAttachments(array $attachments): void
    {
        $this->attachments = new AttachmentsOptions($attachments);
    }
}
