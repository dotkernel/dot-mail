<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

declare(strict_types = 1);

namespace Dot\Mail\Event;

use Dot\Event\Event;
use Dot\Mail\Result\ResultInterface;
use Dot\Mail\Service\MailServiceInterface;

/**
 * Class MailEvent
 * @package Dot\Mail\Event
 */
class MailEvent extends Event
{
    const EVENT_MAIL_PRE_SEND = 'event.mail.pre.send';
    const EVENT_MAIL_POST_SEND = 'event.mail.post.send';
    const EVENT_MAIL_SEND_ERROR = 'event.mail.send.error';

    /** @var  MailServiceInterface */
    protected $mailService;

    /** @var  ResultInterface */
    protected $result;

    /**
     * MailEvent constructor.
     * @param MailServiceInterface $mailService
     * @param string $name
     */
    public function __construct(MailServiceInterface $mailService, string $name = self::EVENT_MAIL_PRE_SEND)
    {
        parent::__construct($name);
        $this->mailService = $mailService;
    }

    /**
     * @return MailServiceInterface
     */
    public function getMailService(): MailServiceInterface
    {
        return $this->mailService;
    }

    /**
     * @param MailServiceInterface $mailService
     */
    public function setMailService(MailServiceInterface $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * @return ResultInterface
     */
    public function getResult(): ResultInterface
    {
        return $this->result;
    }

    /**
     * @param ResultInterface $result
     */
    public function setResult(ResultInterface $result)
    {
        $this->result = $result;
    }
}
