<?php

declare(strict_types=1);

namespace DotTest\Mail\Service;

use Dot\Mail\Email;
use Dot\Mail\Service\LogService;
use DotTest\Mail\CommonTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

use function file_get_contents;
use function is_file;

class LogServiceTest extends TestCase
{
    use CommonTrait;

    private LogService $logService;

    public function setUp(): void
    {
        $this->fileSystem = vfsStream::setup('root', 0644, [
            'log'  => ['mail'],
            'data' => [
                'mail' => [
                    'attachments' => [
                        'testPdfAttachment.pdf' => 'pdf content',
                        'testXlsAttachment.xls' => 'xls content',
                    ],
                    'output'      => [],
                ],
            ],
        ]);

        $this->config     = $this->generateConfig();
        $this->logService = new LogService($this->config['dot_mail']);
    }

    public function testExtractAddresses(): void
    {
        $addressList = [
            new Address('test1@dotkernel.com', 'dot test'),
            new Address('test2@dotkernel.com', 'mail test'),
            new Address('test3@dotkernel.com'),
        ];

        $results = $this->logService->extractAddresses($addressList);

        $this->assertSame(
            ['dot test <test1@dotkernel.com>', 'mail test <test2@dotkernel.com>', ' <test3@dotkernel.com>'],
            $results
        );
    }

    public function testSentMailIsLogged(): void
    {
        $message = (new Email())->subject('testSubject@dotkernel.com');
        $message->addTo('testTo@dotkernel.com', 'To Name');
        $message->addCc('testCc@dotkernel.com');
        $message->addBcc('testBcc@dotkernel.com');

        $this->logService->sent($message);

        $logFile = $this->fileSystem->url() . '/log/mail/sent.log';
        $this->assertTrue(is_file($logFile));
        $contents = file_get_contents($logFile);
        $this->assertIsString($contents);
        $this->assertStringContainsString('"subject":"testSubject@dotkernel.com"', $contents);
        $this->assertStringContainsString('"to":["To Name <testTo@dotkernel.com>"]', $contents);
        $this->assertStringContainsString('"cc":[" <testCc@dotkernel.com>"]', $contents);
        $this->assertStringContainsString('"bcc":[" <testBcc@dotkernel.com>"]', $contents);
    }

    public function testSentReturnsFalseWhenLogDirectoryCannotBeCreated(): void
    {
        // the "directory" of the target is an existing file, so it can be neither used nor created
        $target     = $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf/sent.log';
        $logService = new LogService(['log' => ['sent' => $target]]);
        $message    = new Email();

        $this->assertFalse($logService->sent($message));
    }

    public function testSentIsDisabledWithoutTarget(): void
    {
        $logService = new LogService(['log' => ['sent' => null]]);

        $this->assertNull($logService->sent(new Email()));
    }
}
