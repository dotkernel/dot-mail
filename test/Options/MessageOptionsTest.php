<?php

declare(strict_types=1);

namespace DotTest\Mail\Options;

use Dot\Mail\Options\AttachmentsOptions;
use Dot\Mail\Options\BodyOptions;
use Dot\Mail\Options\MessageOptions;
use PHPUnit\Framework\TestCase;

class MessageOptionsTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $subject = new MessageOptions();

        $from           = "fromTest@dotkernel.com";
        $fromName       = "From Name Test";
        $replyTo        = "retply@dotkernel.com";
        $replyToName    = "Reply To Name Test";
        $to             = ["to@dotkernel.com", "to2@dotkernel.com"];
        $cc             = ["to3@dotkernel.com", "to4@dotkernel.com"];
        $bcc            = ["to5@dotkernel.com", "to6@dotkernel.com"];
        $messageSubject = "Test Subject";
        $body           = [];
        $attachments    = [];

        $subject->setFrom($from);
        $subject->setFromName($fromName);
        $subject->setReplyTo($replyTo);
        $subject->setReplyToName($replyToName);
        $subject->setTo($to);
        $subject->setCc($cc);
        $subject->setBcc($bcc);
        $subject->setSubject($messageSubject);
        $subject->setBody($body);
        $subject->setAttachments($attachments);

        $this->assertSame($from, $subject->getFrom());
        $this->assertSame($fromName, $subject->getFromName());
        $this->assertSame($replyTo, $subject->getReplyTo());
        $this->assertSame($replyToName, $subject->getReplyToName());
        $this->assertSame($to, $subject->getTo());
        $this->assertSame($cc, $subject->getCc());
        $this->assertSame($bcc, $subject->getBcc());
        $this->assertSame($messageSubject, $subject->getSubject());
        $this->assertInstanceOf(BodyOptions::class, $subject->getBody());
        $this->assertInstanceOf(AttachmentsOptions::class, $subject->getAttachments());
    }
}
