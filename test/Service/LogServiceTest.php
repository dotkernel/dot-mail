<?php

declare(strict_types=1);

namespace DotTest\Mail\Service;

use Dot\Mail\Service\LogService;
use DotTest\Mail\CommonTrait;
use Laminas\Mail\AddressList;
use Laminas\Mail\Message;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

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
        $addressList = (new AddressList())->add('test1@dotkernel.com', '  dot test     ')
            ->add('test2@dotkernel.com', ' mail test     ')
            ->add('test3@dotkernel.com');

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
        $message   = $this->createMock(Message::class);
        $toAddress = new AddressList();
        $toAddress->addFromString('testTo@dotkernel.com');
        $ccAddress = new AddressList();
        $ccAddress->addFromString('testCc@dotkernel.com');
        $bccAddress = new AddressList();
        $bccAddress->addFromString('testBcc@dotkernel.com');

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
