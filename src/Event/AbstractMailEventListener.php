<?php

declare(strict_types=1);

namespace Dot\Mail\Event;

use Laminas\EventManager\AbstractListenerAggregate;

abstract class AbstractMailEventListener extends AbstractListenerAggregate implements MailEventListenerInterface
{
    use MailEventListenerTrait;
}
