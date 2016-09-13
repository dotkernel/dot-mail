<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace DotKernel\DotMail\Options;

use DotKernel\DotMail\Exception\InvalidArgumentException;
use Zend\Stdlib\AbstractOptions;

/**
 * Class MessageOptions
 * @package DotKernel\DotMail\Options
 */
class MessageOptions extends AbstractOptions
{
    /** @var string  */
    protected $from = '';

    /** @var string  */
    protected $fromName = '';

    /** @var string  */
    protected $replyTo = '';

    /** @var string  */
    protected $replyToName = '';

    /** @var array  */
    protected $to = [];

    /** @var array  */
    protected $cc = [];

    /** @var array  */
    protected $bcc = [];

    /** @var string  */
    protected $subject = '';

    /** @var  BodyOptions */
    protected $body;

    /** @var  AttachmentsOptions */
    protected $attachments;

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return MessageOptions
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     * @return MessageOptions
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @return string
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @param string $replyTo
     * @return MessageOptions
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getReplyToName()
    {
        return $this->replyToName;
    }

    /**
     * @param string $replyToName
     * @return MessageOptions
     */
    public function setReplyToName($replyToName)
    {
        $this->replyToName = $replyToName;
        return $this;
    }

    /**
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param array $to
     * @return MessageOptions
     */
    public function setTo($to)
    {
        $this->to = (array) $to;
        return $this;
    }

    /**
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @param array $cc
     * @return MessageOptions
     */
    public function setCc($cc)
    {
        $this->cc = (array) $cc;
        return $this;
    }

    /**
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @param array $bcc
     * @return MessageOptions
     */
    public function setBcc($bcc)
    {
        $this->bcc = (array) $bcc;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return MessageOptions
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return BodyOptions
     */
    public function getBody()
    {
        if(!isset($this->body)) {
            $this->setBody([]);
        }

        return $this->body;
    }

    /**
     * @param BodyOptions|array $body
     * @return MessageOptions
     */
    public function setBody($body)
    {
        if (is_array($body)) {
            $this->body = new BodyOptions($body);
        }
        elseif ($body instanceof BodyOptions) {
            $this->body = $body;
        }
        else {
            throw new InvalidArgumentException(sprintf(
                'Body should be an array or an %s, %s provided',
                BodyOptions::class,
                is_object($body) ? get_class($body) : gettype($body)
            ));
        }

        return $this;
    }

    /**
     * @return AttachmentsOptions
     */
    public function getAttachments()
    {
        if (! isset($this->attachments)) {
            $this->setAttachments([]);
        }

        return $this->attachments;
    }

    /**
     * @param AttachmentsOptions|array $attachments
     * @return MessageOptions
     */
    public function setAttachments($attachments)
    {
        if (is_array($attachments)) {
            $this->attachments = new AttachmentsOptions($attachments);
        }
        elseif ($attachments instanceof AttachmentsOptions) {
            $this->attachments = $attachments;
        }
        else {
            throw new InvalidArgumentException(sprintf(
                'Attachments should be an array or an %s, %s provided',
                AttachmentsOptions::class,
                is_object($attachments) ? get_class($attachments) : gettype($attachments)
            ));
        }
        return $this;
    }


}