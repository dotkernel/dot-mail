<?php

declare(strict_types=1);

namespace DotTest\Mail\Event;

use Dot\Mail\Event\MailEventListenerAwareTrait;
use Dot\Mail\Event\MailEventListenerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionObject;

class MailEventListenerTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testListenerDetachingAndClearing(): void
    {
        $trait     = new class {
            use MailEventListenerAwareTrait;
        };
        $listener1 = $this->createMock(MailEventListenerInterface::class);
        $listener2 = $this->createMock(MailEventListenerInterface::class);

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
}
