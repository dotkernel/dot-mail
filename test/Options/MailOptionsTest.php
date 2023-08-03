<?php

declare(strict_types=1);

namespace DotTest\Mail\Options;

use Dot\Mail\Event\AbstractMailEventListener;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Options\MessageOptions;
use Laminas\Mail\Transport\FileOptions;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use PHPUnit\Framework\TestCase;

class MailOptionsTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $subject = new MailOptions();

        $transport             = 'smtp';
        $transportMap          = ['test' => 'array'];
        $messageOptions        = ['from' => '', 'to' => []];
        $smtpOptions           = ['host' => '', 'port' => 587];
        $fileOptions           = [];
        $eventListeners        = [AbstractMailEventListener::class];
        $saveSentMessageFolder = ['INBOX.Sent'];

        $subject->setTransport($transport);
        $subject->setTransportMap($transportMap);
        $subject->setMessageOptions($messageOptions);
        $subject->setSmtpOptions($smtpOptions);
        $subject->setFileOptions($fileOptions);
        $subject->setEventListeners($eventListeners);
        $subject->setSaveSentMessageFolder($saveSentMessageFolder);

        $this->assertSame(Smtp::class, $subject->getTransport());
        $this->assertSame($transportMap, $subject->getTransportMap());
        $this->assertInstanceOf(MessageOptions::class, $subject->getMessageOptions());
        $this->assertInstanceOf(SmtpOptions::class, $subject->getSmtpOptions());
        $this->assertInstanceOf(FileOptions::class, $subject->getFileOptions());
        $this->assertSame($eventListeners, $subject->getEventListeners());
        $this->assertSame($saveSentMessageFolder, $subject->getSaveSentMessageFolder());
    }
}
