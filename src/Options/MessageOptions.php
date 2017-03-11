<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class MessageOptions
 * @package Dot\Mail\Options
 */
class MessageOptions extends AbstractOptions
{
    /** @var string */
    protected $from = '';

    /** @var string */
    protected $fromName = '';

    /** @var string */
    protected $replyTo = '';

    /** @var string */
    protected $replyToName = '';

    /** @var array */
    protected $to = [];

    /** @var array */
    protected $cc = [];

    /** @var array */
    protected $bcc = [];

    /** @var string */
    protected $subject = '';

    /** @var  BodyOptions */
    protected $body;

    /** @var  AttachmentsOptions */
    protected $attachments;

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom(string $from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     */
    public function setFromName(string $fromName)
    {
        $this->fromName = $fromName;
    }

    /**
     * @return string
     */
    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    /**
     * @param string $replyTo
     */
    public function setReplyTo(string $replyTo)
    {
        $this->replyTo = $replyTo;
    }

    /**
     * @return string
     */
    public function getReplyToName(): string
    {
        return $this->replyToName;
    }

    /**
     * @param string $replyToName
     */
    public function setReplyToName(string $replyToName)
    {
        $this->replyToName = $replyToName;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param array $to
     */
    public function setTo(array $to)
    {
        $this->to = $to;
    }

    /**
     * @return array
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @param array $cc
     */
    public function setCc(array $cc)
    {
        $this->cc = $cc;
    }

    /**
     * @return array
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * @param array $bcc
     */
    public function setBcc(array $bcc)
    {
        $this->bcc = $bcc;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return BodyOptions
     */
    public function getBody(): BodyOptions
    {
        if (!isset($this->body)) {
            $this->setBody([]);
        }

        return $this->body;
    }

    /**
     * @param array $body
     */
    public function setBody(array $body)
    {
        $this->body = new BodyOptions($body);
    }

    /**
     * @return AttachmentsOptions
     */
    public function getAttachments(): AttachmentsOptions
    {
        if (!isset($this->attachments)) {
            $this->setAttachments([]);
        }

        return $this->attachments;
    }

    /**
     * @param array $attachments
     */
    public function setAttachments(array $attachments)
    {
        $this->attachments = new AttachmentsOptions($attachments);
    }
}
