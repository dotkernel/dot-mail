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

use Zend\EventManager\EventManagerAwareTrait;

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
