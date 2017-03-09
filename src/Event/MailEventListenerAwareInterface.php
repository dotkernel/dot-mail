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

/**
 * Interface MailListenerAwareInterface
 * @package Dot\Mail\Event
 */
interface MailEventListenerAwareInterface
{
    /**
     * @param MailEventListenerInterface $listener
     * @param int $priority
     */
    public function attachListener(MailEventListenerInterface $listener, $priority = 1);

    /**
     * @param MailEventListenerInterface $listener
     */
    public function detachListener(MailEventListenerInterface $listener);

    /**
     * Detach and clear listeners array
     */
    public function clearListeners();
}
