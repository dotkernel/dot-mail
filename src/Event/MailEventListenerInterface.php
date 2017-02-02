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

use Zend\EventManager\ListenerAggregateInterface;

/**
 * Interface MailListenerInterface
 * @package Dot\Mail\Event
 */
interface MailEventListenerInterface extends ListenerAggregateInterface
{
    /**
     * @param MailEvent $e
     * @return mixed
     */
    public function onPreSend(MailEvent $e);

    /**
     * @param MailEvent $e
     * @return mixed
     */
    public function onPostSend(MailEvent $e);

    /**
     * @param MailEvent $e
     * @return mixed
     */
    public function onSendError(MailEvent $e);
}
