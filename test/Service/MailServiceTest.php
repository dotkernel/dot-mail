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
use Laminas\Mail\Protocol\Exception\RuntimeException as ProtocolRuntimeException;
use Laminas\Mail\Storage\Folder;
use Laminas\Mail\Storage\Imap;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Part;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MailServiceTest extends TestCase
{
    use CommonTrait;

    private MailService $mailService;
    private Message|MockObject $message;
    private TransportInterface|MockObject $transportInterface;
    private MailOptions|MockObject $mailOptions;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->message            = new Message();
        $this->transportInterface = $this->createMock(TransportInterface::class);
        $this->mailOptions        = $this->createMock(MailOptions::class);
        $logServiceInterface      = $this->createMock(LogServiceInterface::class);

        $this->mailService = new MailService(
            $logServiceInterface,
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

    /**
     * @throws Exception
     */
    public function testGettersAndSetters(): void
    {
        $attachments = ['/testAttachment.pdf', '/testDirectory/testAttachment2.xls'];
        $storage     = $this->createMock(Imap::class);
        $transport   = $this->createMock(Sendmail::class);

        $this->mailService->setAttachments($attachments);
        $this->mailService->setStorage($storage);
        $this->mailService->setTransport($transport);

        $this->assertSame($attachments, $this->mailService->getAttachments());
        $this->assertContains('/testAttachment.pdf', $this->mailService->getAttachments());
        $this->assertSame($storage, $this->mailService->getStorage());
        $this->assertSame($transport, $this->mailService->getTransport());
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

    public function testMailResultCreatedFromException(): void
    {
        $customException = new RuntimeException('Custom exception test');

        $mailResult = $this->mailService->createMailResultFromException($customException);

        $this->assertInstanceOf(MailResult::class, $mailResult);
        $this->assertSame($customException, $mailResult->getException());
        $this->assertSame('Custom exception test', $mailResult->getMessage());
    }

    /**
     * @throws Exception
     */
    public function testGetFolderNames(): void
    {
        $childFolder = $this->createMock(Folder::class);
        $childFolder->expects(self::once())
            ->method('getGlobalName')
            ->willReturn('rootFolderName.childFolderName');

        $rootFolder = $this->createMock(Folder::class);
        $rootFolder->expects(self::once())
            ->method('getChildren')
            ->willReturn([$childFolder]);

        $rootFolder->expects(self::once())
            ->method('getGlobalName')
            ->willReturn('rootFolderName');

        $storage = $this->createMock(Imap::class);
        $storage->expects(self::exactly(2))
            ->method('getFolders')
            ->willReturnOnConsecutiveCalls([$rootFolder], $rootFolder);

        $this->mailService->setStorage($storage);
        $result = $this->mailService->getFolderGlobalNames();

        $this->assertCount(2, $result);
        $this->assertContains('rootFolderName', $result);
        $this->assertContains('rootFolderName.childFolderName', $result);
    }

    /**
     * @throws Exception
     */
    public function testCreateStorageThrowsRuntimeExceptionWithInvalidConfig(): void
    {
        $smtpOptions = $this->createMock(SmtpOptions::class);
        $smtpOptions->expects(self::once())
            ->method('getHost')
            ->willReturn('127.0.0.1');

        $smtpOptions->expects(self::once())
            ->method('getConnectionConfig')
            ->willReturn([
                'username' => 'testUsername',
                'password' => 'testPassword',
                'ssl'      => 'ssl',
            ]);

        $this->mailOptions->expects(self::atMost(2))
            ->method('getSmtpOptions')
            ->willReturn($smtpOptions);

        $this->expectException(ProtocolRuntimeException::class);
        $this->mailService->createStorage();
    }

    /**
     * @throws Exception
     */
    public function testCreateStorageReturnsImap(): void
    {
        $imap        = $this->createMock(Imap::class);
        $mailService = $this->createPartialMock(MailService::class, ['createStorage']);
        $mailService->expects(self::once())
            ->method('createStorage')
            ->willReturn($imap);

        $this->assertInstanceOf(Imap::class, $mailService->createStorage());
    }
}
