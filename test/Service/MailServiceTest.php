<?php

declare(strict_types=1);

namespace DotTest\Mail\Service;

use Dot\Mail\Event\MailEvent;
use Dot\Mail\Exception\MailException;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Result\MailResult;
use Dot\Mail\Service\LogServiceInterface;
use Dot\Mail\Service\MailService;
use DotTest\Mail\CommonTrait;
use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Part;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MailServiceTest extends TestCase
{
    use CommonTrait;

    private MailService $mailService;
    private LogServiceInterface|MockObject $logServiceInterface;
    private Message|MockObject $message;
    private TransportInterface|MockObject $transportInterface;
    private MailOptions|MockObject $mailOptions;

    public function setUp(): void
    {
        $this->logServiceInterface = $this->createMock(LogServiceInterface::class);
        $this->message             = new Message();
        $this->transportInterface  = $this->createMock(TransportInterface::class);
        $this->mailOptions         = $this->createMock(MailOptions::class);

        $this->mailService = new MailService(
            $this->logServiceInterface,
            $this->message,
            $this->transportInterface,
            $this->mailOptions
        );

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

        $this->config = $this->generateConfig();
    }

    public function testCreateMailEvent(): void
    {
        $defaultMailEvent = $this->mailService->createMailEvent();
        $this->assertInstanceOf(MailEvent::class, $defaultMailEvent);
        $this->assertSame(MailEvent::EVENT_MAIL_PRE_SEND, $defaultMailEvent->getName());

        $result    = new MailResult();
        $mailEvent = $this->mailService->createMailEvent('testName', $result);
        $this->assertInstanceOf(MailEvent::class, $mailEvent);
        $this->assertSame('testName', $mailEvent->getName());
        $this->assertSame(MailResult::DEFAULT_MESSAGE, $mailEvent->getResult()->getMessage());
    }

    public function testAttachFilesToStringBody(): void
    {
        $this->mailService->setSubject('Test Subject');
        $this->message->setBody('Body as string test');

        $this->mailService->addAttachment($this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf');
        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testXlsAttachment.xls',
            'spreadsheetName'
        );

        $result = $this->mailService->attachFiles();
        $this->assertInstanceOf(Message::class, $result);
        $this->assertSame('Test Subject', $result->getSubject());
    }

    public function testAttachFilesToMimeMessageBody(): void
    {
        $stringMessage = '<div>
            <h1>Message header</h1>
            <div>
                <p>Message body</p>
            </div>
        </div>';

        $mimeMessage = new MimeMessage();
        $mimeMessage->setParts([new Part($stringMessage)]);
        $this->mailService->setSubject('Test Subject');
        $this->message->setBody($mimeMessage);
        $this->mailService->addAttachments([
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf',
            $this->fileSystem->url() . '/data/mail/attachments/testXlsAttachment.xls',
        ]);

        $result = $this->mailService->attachFiles();
        $this->assertInstanceOf(Message::class, $result);
        $this->assertSame('Test Subject', $result->getSubject());
    }

    public function testSendFailureTriggersErrorEvent(): void
    {
        $exception = new RuntimeException("Test Error Message");
        $this->transportInterface->expects(self::once())
            ->method('send')
            ->willThrowException($exception);

        $this->expectException(MailException::class);
        $this->expectExceptionMessage("Test Error Message");
        $this->mailService->send();
    }
}
