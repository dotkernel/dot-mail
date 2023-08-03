<?php

declare(strict_types=1);

namespace DotTest\Mail\Options;

use Dot\Mail\Options\BodyOptions;
use PHPUnit\Framework\TestCase;

class BodyOptionsTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $subject = new BodyOptions();
        $subject->setCharset('test');
        $subject->setContent('Test content');

        $this->assertSame('test', $subject->getCharset());
        $this->assertSame('Test content', $subject->getContent());
    }
}
