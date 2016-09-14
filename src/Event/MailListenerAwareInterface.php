<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace Dot\Mail\Event;

/**
 * Interface MailListenerAwareInterface
 * @package Dot\Mail\Event
 */
interface MailListenerAwareInterface
{
    /**
     * @param MailListenerInterface $listener
     * @param int $priority
     * @return mixed
     */
    public function attachMailListener(MailListenerInterface $listener, $priority = 1);

    /**
     * @param MailListenerInterface $listener
     * @return mixed
     */
    public function detachMailListener(MailListenerInterface $listener);

    /**
     * @return mixed
     */
    public function clearMailListeners();
}