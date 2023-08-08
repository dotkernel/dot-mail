<?php

declare(strict_types=1);

namespace Dot\Mail\Event;

interface MailEventListenerAwareInterface
{
    public function attachListener(MailEventListenerInterface $listener, int $priority = 1): void;

    public function detachListener(MailEventListenerInterface $listener): void;

    public function clearListeners(): void;
}
