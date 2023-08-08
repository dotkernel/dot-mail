<?php

declare(strict_types=1);

namespace Dot\Mail\Event;

use ArrayAccess;
use Dot\Event\Event;
use Dot\Mail\Result\ResultInterface;
use Dot\Mail\Service\MailServiceInterface;

/**
 * @template TTarget of object|string|null
 * @template TParams of array|ArrayAccess|object
 * @extends Event<TTarget, TParams>
 */
class MailEvent extends Event
{
    public const EVENT_MAIL_PRE_SEND   = 'event.mail.pre.send';
    public const EVENT_MAIL_POST_SEND  = 'event.mail.post.send';
    public const EVENT_MAIL_SEND_ERROR = 'event.mail.send.error';

    protected MailServiceInterface $mailService;
    protected ResultInterface $result;

    public function __construct(MailServiceInterface $mailService, string $name = self::EVENT_MAIL_PRE_SEND)
    {
        parent::__construct($name);
        $this->mailService = $mailService;
    }

    public function getMailService(): MailServiceInterface
    {
        return $this->mailService;
    }

    public function setMailService(MailServiceInterface $mailService): void
    {
        $this->mailService = $mailService;
    }

    public function getResult(): ResultInterface
    {
        return $this->result;
    }

    public function setResult(ResultInterface $result): void
    {
        $this->result = $result;
    }
}
