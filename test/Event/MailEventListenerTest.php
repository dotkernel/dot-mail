<?php

declare(strict_types=1);

namespace DotTest\Mail\Event;

use Dot\Mail\Event\MailEventListenerAwareTrait;
use Dot\Mail\Event\MailEventListenerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionObject;

class MailEventListenerTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testListenerDetachingAndClearing(): void
    {
        $trait     = new class {
            use MailEventListenerAwareTrait;
        };
        $listener1 = $this->createStub(MailEventListenerInterface::class);
        $listener2 = $this->createStub(MailEventListenerInterface::class);

        $trait->attachListener($listener1, 10);
        $trait->attachListener($listener2);

        $ref               = new ReflectionObject($trait);
        $listenersProperty = $ref->getProperty('listeners');
        $listeners         = $listenersProperty->getValue($trait);
        $this->assertCount(2, $listeners);
        $this->assertContainsEquals($listener1, $listeners);
        $this->assertContainsEquals($listener2, $listeners);

        $trait->detachListener($listener2);
        $listeners = $listenersProperty->getValue($trait);
        $this->assertContainsEquals($listener1, $listeners);

        $trait->clearListeners();
        $listeners = $listenersProperty->getValue($trait);
        $this->assertEmpty($listeners);
    }

    /**
     * @throws ReflectionException
     */
    public function testDetachingUnknownListenerKeepsOthers(): void
    {
        $trait    = new class {
            use MailEventListenerAwareTrait;
        };
        $attached = $this->createStub(MailEventListenerInterface::class);
        $unknown  = $this->createStub(MailEventListenerInterface::class);

        $trait->attachListener($attached);
        $trait->detachListener($unknown);

        $listenersProperty = (new ReflectionObject($trait))->getProperty('listeners');
        $this->assertSame([$attached], $listenersProperty->getValue($trait));
    }
}
