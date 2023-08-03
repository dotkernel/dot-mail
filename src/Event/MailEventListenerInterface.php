<?php

declare(strict_types=1);

namespace Dot\Mail\Event;

use Laminas\EventManager\ListenerAggregateInterface;

interface MailEventListenerInterface extends ListenerAggregateInterface
{
    public function onPreSend(MailEvent $e);

    public function onPostSend(MailEvent $e);

    public function onSendError(MailEvent $e);
}
