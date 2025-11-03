<?php

declare(strict_types=1);

namespace DotTest\Mail\Service;

use Dot\Mail\Email;
use Dot\Mail\Event\MailEvent;
use Dot\Mail\Exception\MailException;
use Dot\Mail\Exception\RuntimeException;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Result\MailResult;
use Dot\Mail\Service\LogServiceInterface;
use Dot\Mail\Service\MailService;
use DotTest\Mail\CommonTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Part\TextPart;

class MailServiceTest extends TestCase
{
    use CommonTrait;

    private MailService $mailService;
    private Email|MockObject $message;
    private TransportInterface|MockObject $transportInterface;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->message            = new Email();
        $this->transportInterface = $this->createMock(TransportInterface::class);
        $mailOptions              = $this->createMock(MailOptions::class);
        $logServiceInterface      = $this->createMock(LogServiceInterface::class);

        $this->mailService = new MailService(
            $logServiceInterface,
            $this->message,
            $this->transportInterface,
            $mailOptions
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

    /**
     * @throws Exception
     */
    public function testGettersAndSetters(): void
    {
        $attachments = ['/testAttachment.pdf', '/testDirectory/testAttachment2.xls'];
        $transport   = $this->createMock(EsmtpTransport::class);

        $this->mailService->setAttachments($attachments);
        $this->mailService->setTransport($transport);

        $this->assertSame($attachments, $this->mailService->getAttachments());
        $this->assertContains('/testAttachment.pdf', $this->mailService->getAttachments());
        $this->assertSame($transport, $this->mailService->getTransport());
    }

    public function testCreateMailEvent(): void
    {
        $defaultMailEvent = $this->mailService->createMailEvent();
        $this->assertContainsOnlyInstancesOf(MailEvent::class, [$defaultMailEvent]);
        $this->assertSame(MailEvent::EVENT_MAIL_PRE_SEND, $defaultMailEvent->getName());

        $result    = new MailResult();
        $mailEvent = $this->mailService->createMailEvent('testName', $result);
        $this->assertContainsOnlyInstancesOf(MailEvent::class, [$mailEvent]);
        $this->assertSame('testName', $mailEvent->getName());
        $this->assertSame(MailResult::DEFAULT_MESSAGE, $mailEvent->getResult()->getMessage());
    }

    public function testAttachFilesToStringBody(): void
    {
        $this->mailService->setSubject('Test Subject');
        $this->message->html('Body as string test');

        $this->mailService->addAttachment($this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf');
        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testXlsAttachment.xls',
            'spreadsheetName'
        );

        $result = $this->mailService->attachFiles();
        $this->assertInstanceOf(Email::class, $result);
        $this->assertSame('Test Subject', $result->getSubject());
        $this->assertCount(2, $this->mailService->getAttachments());
        $this->assertArrayHasKey('spreadsheetName', $this->mailService->getAttachments());
    }

    public function testAttachFilesToMimeMessageBody(): void
    {
        $stringMessage = '<div>
            <h1>Message header</h1>
            <div>
                <p>Message body</p>
            </div>
        </div>';

        $mimeMessage = new TextPart($stringMessage);
        $this->mailService->setSubject('Test Subject');
        $this->mailService->setBody($mimeMessage);
        $this->mailService->addAttachments([
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf',
            $this->fileSystem->url() . '/data/mail/attachments/testXlsAttachment.xls',
        ]);

        $result = $this->mailService->attachFiles();
        $this->assertInstanceOf(Email::class, $result);
        $this->assertSame('Test Subject', $result->getSubject());
    }

    public function testSendFailureTriggersErrorEvent(): void
    {
        $exception = new RuntimeException("Test Error Message");
        $this->transportInterface->expects($this->once())
            ->method('send')
            ->willThrowException($exception);

        $this->expectException(MailException::class);
        $this->expectExceptionMessage("Test Error Message");
        $this->mailService->send();
    }

    public function testMailResultCreatedFromException(): void
    {
        $customException = new RuntimeException('Custom exception test');

        $mailResult = $this->mailService->createMailResultFromException($customException);

        $this->assertInstanceOf(MailResult::class, $mailResult);
        $this->assertSame($customException, $mailResult->getException());
        $this->assertSame('Custom exception test', $mailResult->getMessage());
    }
}
