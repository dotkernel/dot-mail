<?php

declare(strict_types=1);

namespace DotTest\Mail\Options;

use Dot\Mail\Event\AbstractMailEventListener;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Options\MessageOptions;
use Dot\Mail\Options\SmtpOptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MailOptionsTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $subject = new MailOptions();

        $transport      = 'esmtp';
        $transportMap   = ['test' => 'array'];
        $messageOptions = ['from' => '', 'to' => []];
        $smtpOptions    = ['host' => '', 'port' => 587];
        $eventListeners = [AbstractMailEventListener::class];

        $subject->setTransport($transport);
        $subject->setTransportMap($transportMap);
        $subject->setMessageOptions($messageOptions);
        $subject->setSmtpOptions($smtpOptions);
        $subject->setEventListeners($eventListeners);

        $this->assertSame(EsmtpTransport::class, $subject->getTransport());
        $this->assertSame($transportMap, $subject->getTransportMap());
        $this->assertContainsOnlyInstancesOf(MessageOptions::class, [$subject->getMessageOptions()]);
        $this->assertContainsOnlyInstancesOf(SmtpOptions::class, [$subject->getSmtpOptions()]);
        $this->assertSame($eventListeners, $subject->getEventListeners());
    }
}
