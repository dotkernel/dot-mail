<?php

declare(strict_types=1);

namespace Dot\Mail\Event;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateTrait;

trait MailEventListenerTrait
{
    use ListenerAggregateTrait;

    /**
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_PRE_SEND, [$this, 'onPreSend'], $priority);
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_POST_SEND, [$this, 'onPostSend'], $priority);
        $this->listeners[] = $events->attach(MailEvent::EVENT_MAIL_SEND_ERROR, [$this, 'onSendError'], $priority);
    }

    /**
     * @codeCoverageIgnore
     */
    public function onPreSend(MailEvent $e): void
    {
        //NO-OP: left to implementors
    }

    /**
     * @codeCoverageIgnore
     */
    public function onPostSend(MailEvent $e): void
    {
        //NO-OP: left to implementors
    }

    /**
     * @codeCoverageIgnore
     */
    public function onSendError(MailEvent $e): void
    {
        //NO-OP: left to implementors
    }
}
