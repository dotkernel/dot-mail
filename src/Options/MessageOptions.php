<?php

declare(strict_types=1);

namespace Dot\Mail\Options;

use Laminas\Stdlib\AbstractOptions;

/**
 * @extends AbstractOptions<mixed>
 */
class MessageOptions extends AbstractOptions
{
    protected string $from        = '';
    protected string $fromName    = '';
    protected string $replyTo     = '';
    protected string $replyToName = '';
    /** @var string[] */
    protected array $to = [];
    /** @var string[] */
    protected array $cc = [];
    /** @var string[] */
    protected array $bcc      = [];
    protected string $subject = '';
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

    /**
     * @return string[]
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param string[] $to
     */
    public function setTo(array $to): void
    {
        $this->to = $to;
    }

    /**
     * @return string[]
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @param string[] $cc
     */
    public function setCc(array $cc): void
    {
        $this->cc = $cc;
    }

    /**
     * @return string[]
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * @param string[] $bcc
     */
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
        if (! isset($this->body)) {
            $this->body = new BodyOptions();
        }
        return $this->body;
    }

    /**
     * @param array<string, mixed> $body
     */
    public function setBody(array $body): void
    {
        $this->body = new BodyOptions($body);
    }

    public function getAttachments(): AttachmentsOptions
    {
        if (! isset($this->attachments)) {
            $this->attachments = new AttachmentsOptions();
        }
        return $this->attachments;
    }

    /**
     * @param array<string, mixed> $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = new AttachmentsOptions($attachments);
    }
}
