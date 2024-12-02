<?php

declare(strict_types=1);

namespace DotTest\Mail;

use DateTimeImmutable;
use Dot\Mail\Email;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\LogicException;

class EmailTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $subject = new Email();

        $subject->date(new DateTimeImmutable('now'));
        $subject->returnPath('test@gmail.com');
        $subject->setSender('test@gmail.com', 'testSender');
        $subject->addFrom(['testAddFrom@gmail.com', 'testAddFrom@gmail2.com'], 'testAddFrom');
        $subject->setFrom('testSetFrom@gmail.com', 'testSetFrom');
        $subject->addReplyTo('testaddReplyTo@gmail.com', 'testAddReplyTo');
        $subject->setReplyTo('testSetReplyTo@gmail.com', 'testSetReplyTo');
        $subject->addTo('testAddTo@gmail.com', 'testAddTo');
        $subject->setTo('testAddTo@gmail.com', 'testAddTo');
        $subject->addCc('testAddCc@gmail.com', 'testAddCc');
        $subject->setCc(new Address('testSetCc@gmail.com'), 'testSetBcc');
        $subject->addBcc('testAddBcc@gmail.com', 'testAddBcc');
        $subject->setBcc('testSetBcc@gmail.com', 'testSetBcc');
        $subject->priority(6);
        $subject->text('test text body');
        $subject->html('test html body');
        $subject->setEncoding('UTF-8');

        $this->assertInstanceOf(DateTimeImmutable::class, $subject->getDate());
        $this->assertInstanceOf(Address::class, $subject->getReturnPath());
        $this->assertInstanceOf(Address::class, $subject->getSender());
        $this->assertIsArray($subject->getFrom());
        $this->assertIsArray($subject->getReplyTo());
        $this->assertIsArray($subject->getTo());
        $this->assertIsArray($subject->getCc());
        $this->assertIsArray($subject->getBcc());
        $this->assertIsInt($subject->getPriority());
        $this->assertIsString($subject->getTextBody());
        $this->assertIsString($subject->getHtmlBody());
        $this->assertIsString($subject->getEncoding());
        $this->assertIsString($subject->getHtmlCharset());
        $this->assertIsString($subject->getTextCharset());
    }

    public function testEnsureBodyValidity(): void
    {
        $subject = new Email();
        $this->expectException(LogicException::class);
        $subject->ensureValidity();
    }

    public function testEnsureValidity(): void
    {
        $subject = new Email();
        $subject->html('test html body');
        $subject->getHeaders()->addHeader('X-Unsent', '1');

        $this->expectException(LogicException::class);
        $subject->ensureValidity();
    }
}
