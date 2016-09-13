<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace DotKernel\DotMail\Event;

use Zend\EventManager\ListenerAggregateInterface;

/**
 * Interface MailListenerInterface
 * @package DotKernel\DotMail\Event
 */
interface MailListenerInterface extends ListenerAggregateInterface
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