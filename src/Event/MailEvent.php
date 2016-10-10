<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

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
    public function __construct(MailServiceInterface $mailService, $name = self::EVENT_MAIL_PRE_SEND)
    {
        parent::__construct($name);
        $this->mailService = $mailService;
    }

    /**
     * @return MailServiceInterface
     */
    public function getMailService()
    {
        return $this->mailService;
    }

    /**
     * @param MailServiceInterface $mailService
     * @return MailEvent
     */
    public function setMailService($mailService)
    {
        $this->mailService = $mailService;
        return $this;
    }

    /**
     * @return ResultInterface
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param ResultInterface $result
     * @return MailEvent
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }


}