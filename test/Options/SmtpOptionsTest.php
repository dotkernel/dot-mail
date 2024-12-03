<?php

declare(strict_types=1);

namespace DotTest\Mail\Options;

use Dot\Mail\Options\SmtpOptions;
use DotTest\Mail\CommonTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\InvalidArgumentException;

class SmtpOptionsTest extends TestCase
{
    use CommonTrait;

    public function testSmtpGettersAndSetters(): void
    {
        $subject = new SmtpOptions();

        $subject->setName('smtpTest');
        $subject->setConnectionTimeLimit(123);

        $this->assertIsString($subject->getName());
        $this->assertIsArray($subject->getConnectionConfig());
        $this->assertIsString($subject->getHost());
        $this->assertIsString($subject->getConnectionClass());
        $this->assertIsInt($subject->getPort());
        $this->assertIsInt($subject->getConnectionTimeLimit());

        $this->expectException(InvalidArgumentException::class);
        $subject->setPort(0);
    }
}
