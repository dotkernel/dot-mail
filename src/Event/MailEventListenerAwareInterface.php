<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
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
