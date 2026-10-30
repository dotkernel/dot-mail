<?php

declare(strict_types=1);

namespace DotTest\Mail;

use DateTimeImmutable;
use Dot\Mail\Email;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\LogicException;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\TextPart;

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

    public function testAddressObjectsInArraysAreKept(): void
    {
        $subject = new Email();

        $subject->addTo([new Address('first@example.com', 'First'), 'second@example.com'], 'Ignored');
        $subject->setCc([new Address('cc@example.com', 'Copy')]);

        $to = $subject->getTo();
        $this->assertCount(2, $to);
        $this->assertSame('first@example.com', $to[0]->getAddress());
        $this->assertSame('First', $to[0]->getName());
        $this->assertSame('second@example.com', $to[1]->getAddress());
        $this->assertSame('Ignored', $to[1]->getName());

        $cc = $subject->getCc();
        $this->assertCount(1, $cc);
        $this->assertSame('Copy', $cc[0]->getName());
    }

    public function testTextOnlyMessageHasOnlyTextPart(): void
    {
        $subject = new Email();
        $subject->text('plain text');

        $body = $subject->getBody();
        $this->assertSame(TextPart::class, $body::class);
        $this->assertSame('plain text', $body->getBody());
    }

    public function testTextAndHtmlMessageIsAlternative(): void
    {
        $subject = new Email();
        $subject->text('plain text');
        $subject->html('<p>html</p>');

        $this->assertInstanceOf(AlternativePart::class, $subject->getBody());
    }

    public function testMessageWithoutBodyIsInvalid(): void
    {
        $subject = new Email();
        $subject->setFrom('from@example.com');
        $subject->setTo('to@example.com');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A message must have a text or an HTML part.');
        $subject->ensureValidity();
    }
}
