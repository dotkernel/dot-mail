<?php

declare(strict_types=1);

namespace DotTest\Mail\Event;

use Dot\Mail\Event\MailEvent;
use Dot\Mail\Service\MailServiceInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class MailEventTest extends TestCase
{
    private MailServiceInterface|Stub $mailService;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->mailService = $this->createStub(MailServiceInterface::class);
    }

    /**
     * @throws Exception
     */
    public function testMailEventCanBeCreated(): void
    {
        $defaultMailEvent = new MailEvent($this->mailService);

        $this->assertContainsOnlyInstancesOf(MailEvent::class, [$defaultMailEvent]);
        $this->assertContainsOnlyInstancesOf(MailServiceInterface::class, [$defaultMailEvent->getMailService()]);

        $customMailEvent      = new MailEvent($this->mailService, MailEvent::EVENT_MAIL_SEND_ERROR);
        $mailServiceInterface = $this->createStub(MailServiceInterface::class);
        $customMailEvent->setMailService($mailServiceInterface);

        $this->assertContainsOnlyInstancesOf(MailEvent::class, [$customMailEvent]);
        $this->assertSame(MailEvent::EVENT_MAIL_SEND_ERROR, $customMailEvent->getName());
        $this->assertContainsOnlyInstancesOf(MailServiceInterface::class, [$customMailEvent->getMailService()]);
    }

    public function testEventPropagation(): void
    {
        $event = new MailEvent($this->mailService);
        $this->assertFalse($event->propagationIsStopped());
        $event->stopPropagation();
        $this->assertTrue($event->propagationIsStopped());
    }
}
