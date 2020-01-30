<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Event;

use Laminas\EventManager\AbstractListenerAggregate;

/**
 * Class AbstractMailListener
 * @package Dot\Mail\Event
 */
abstract class AbstractMailEventListener extends AbstractListenerAggregate implements MailEventListenerInterface
{
    use MailEventListenerTrait;
}
