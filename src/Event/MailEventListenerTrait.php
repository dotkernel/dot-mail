<?php
/**
 * @copyright: DotKernel
 * @library: dot-mail
 * @author: n3vrax
 * Date: 2/21/2017
 * Time: 11:23 PM
 */

declare(strict_types = 1);

namespace Dot\Mail\Event;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;

/**
 * Class MailEventListenerTrait
 * @package Dot\Mail\Event
 */
trait MailEventListenerTrait
{
    use ListenerAggregateTrait;

    /**
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_PRE_SEND, [$this, 'onPreSend'], $priority);
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_POST_SEND, [$this, 'onPostSend'], $priority);
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_SEND_ERROR, [$this, 'onSendError'], $priority);
    }

    public function onPreSend(MailEvent $e)
    {
        //NO-OP: left to implementors
    }

    public function onPostSend(MailEvent $e)
    {
        //NO-OP: left to implementors
    }

    public function onSendError(MailEvent $e)
    {
        //NO-OP: left to implementors
    }
}
