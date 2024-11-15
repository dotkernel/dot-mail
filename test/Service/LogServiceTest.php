<?php

declare(strict_types=1);

namespace DotTest\Mail\Service;

use Dot\Mail\Email;
use Dot\Mail\Service\LogService;
use DotTest\Mail\CommonTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Exception;
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

    /**
     * @throws Exception
     */
    public function testSentMailIsLogged(): void
    {
        $message    = $this->createMock(Email::class);
        $toAddress  = [new Address('testTo@dotkernel.com')];
        $ccAddress  = [new Address('testCc@dotkernel.com')];
        $bccAddress = [new Address('testBcc@dotkernel.com')];

        $message->expects($this->once())->method('getSubject')
            ->willReturn('testSubject@dotkernel.com');
        $message->expects($this->once())->method('getTo')
            ->willReturn($toAddress);
        $message->expects($this->once())->method('getCc')
            ->willReturn($ccAddress);
        $message->expects($this->once())->method('getBcc')
            ->willReturn($bccAddress);

        $this->logService->sent($message);

        $this->assertTrue(is_file($this->config['dot_mail']['log']['sent']));
        $this->assertStringContainsString(
            '"subject":"testSubject@dotkernel.com"',
            file_get_contents($this->fileSystem->url() . '/log/mail/sent.log')
        );
    }
}
