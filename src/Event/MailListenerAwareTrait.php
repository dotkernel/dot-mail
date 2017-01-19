<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace Dot\Mail\Event;

use Zend\EventManager\EventManagerAwareTrait;

/**
 * Class MailListenerAwareTrait
 * @package Dot\Mail\Event
 */
trait MailListenerAwareTrait
{
    use EventManagerAwareTrait;

    /** @var MailListenerInterface[] */
    protected $listeners = [];

    /**
     * @param MailListenerInterface $listener
     * @param int $priority
     * @return $this
     */
    public function attachMailListener(MailListenerInterface $listener, $priority = 1)
    {

        $listener->attach($this->getEventManager(), $priority);
        $this->listeners[] = $listener;
        return $this;
    }

    /**
     * @param MailListenerInterface $listener
     * @return $this
     */
    public function detachMailListener(MailListenerInterface $listener)
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
        return $this;
    }

    /**
     * @return $this
     */
    public function clearMailListeners()
    {
        foreach ($this->listeners as $listener) {
            $listener->detach($this->getEventManager());
        }

        $this->listeners = [];
        return $this;
    }
}
