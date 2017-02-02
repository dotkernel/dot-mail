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

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

/**
 * Class AbstractMailListener
 * @package Dot\Mail\Event
 */
abstract class AbstractMailEventListener extends AbstractListenerAggregate implements MailEventListenerInterface
{
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
