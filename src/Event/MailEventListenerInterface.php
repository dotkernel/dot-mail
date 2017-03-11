<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
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
