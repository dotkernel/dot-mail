<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Event;

use Laminas\EventManager\EventManagerAwareTrait;

/**
 * Class MailListenerAwareTrait
 * @package Dot\Mail\Event
 */
trait MailEventListenerAwareTrait
{
    use EventManagerAwareTrait;

    /** @var MailEventListenerInterface[] */
    protected $listeners = [];

    /**
     * @param MailEventListenerInterface $listener
     * @param int $priority
     */
    public function attachListener(MailEventListenerInterface $listener, $priority = 1)
    {

        $listener->attach($this->getEventManager(), $priority);
        $this->listeners[] = $listener;
    }

    /**
     * @param MailEventListenerInterface $listener
     */
    public function detachListener(MailEventListenerInterface $listener)
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

    /**
     * Detach an clear listeners array
     */
    public function clearListeners()
    {
        foreach ($this->listeners as $listener) {
            $listener->detach($this->getEventManager());
        }

        $this->listeners = [];
    }
}
