<?php

declare(strict_types=1);

namespace Dot\Mail\Event;

use Laminas\EventManager\EventManagerAwareTrait;

trait MailEventListenerAwareTrait
{
    use EventManagerAwareTrait;

    protected array $listeners = [];

    public function attachListener(MailEventListenerInterface $listener, int $priority = 1): void
    {
        $listener->attach($this->getEventManager(), $priority);
        $this->listeners[] = $listener;
    }

    public function detachListener(MailEventListenerInterface $listener): void
    {
        $listener->detach($this->getEventManager());

        $idx = 0;
        foreach ($this->listeners as $l) {
            if ($l === $listener) {
                break;
            }

            $idx++;
        }

        unset($this->listeners[$idx]);
    }

    public function clearListeners(): void
    {
        foreach ($this->listeners as $listener) {
            $listener->detach($this->getEventManager());
        }

        $this->listeners = [];
    }
}
