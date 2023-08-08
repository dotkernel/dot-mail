<?php

declare(strict_types=1);

namespace DotTest\Mail\Options;

use Dot\Mail\Options\AttachmentsOptions;
use PHPUnit\Framework\TestCase;

class AttachmentOptionsTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $subject = new AttachmentsOptions();
        $subject->setDir([]);
        $subject->setFiles([]);

        $this->assertArrayHasKey('iterate', $subject->getDir());
        $this->assertArrayHasKey('path', $subject->getDir());
        $this->assertArrayHasKey('recursive', $subject->getDir());
        $this->assertSame([], $subject->getFiles());

        $subject->addFile('testPath');
        $subject->addFiles(['testPath2']);

        $this->assertContains('testPath', $subject->getFiles());
        $this->assertContains('testPath2', $subject->getFiles());
    }
}
