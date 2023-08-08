<?php

declare(strict_types=1);

namespace DotTest\Mail\Result;

use Dot\Mail\Result\MailResult;
use Exception;
use PHPUnit\Framework\TestCase;

class MailResultTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $message   = 'Error sending mail test!';
        $exception = new Exception('Error message test');

        $mailResult = new MailResult(
            false,
            $message,
            $exception
        );

        $this->assertSame(false, $mailResult->isValid());
        $this->assertSame($message, $mailResult->getMessage());
        $this->assertSame($exception, $mailResult->getException());
        $this->assertSame($exception->getMessage(), $mailResult->getException()->getMessage());
        $this->assertTrue($mailResult->hasException());
    }
}
