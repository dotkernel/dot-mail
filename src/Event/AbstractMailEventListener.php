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

use Zend\EventManager\AbstractListenerAggregate;

/**
 * Class AbstractMailListener
 * @package Dot\Mail\Event
 */
abstract class AbstractMailEventListener extends AbstractListenerAggregate implements MailEventListenerInterface
{
    use MailEventListenerTrait;
}
