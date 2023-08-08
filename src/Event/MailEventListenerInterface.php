<?php

declare(strict_types=1);

namespace Dot\Mail\Event;

use Laminas\EventManager\ListenerAggregateInterface;

interface MailEventListenerInterface extends ListenerAggregateInterface
{
    public function onPreSend(MailEvent $e): void;

    public function onPostSend(MailEvent $e): void;

    public function onSendError(MailEvent $e): void;
}
