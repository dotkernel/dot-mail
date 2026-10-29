<?php

declare(strict_types=1);

namespace DotTest\Mail\Service;

use Dot\Mail\Email;
use Dot\Mail\Event\AbstractMailEventListener;
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
use ReflectionClass;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Symfony\Component\Mime\Part\TextPart;
use Throwable;
use TypeError;

use function file_put_contents;
use function mkdir;
use function sort;

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

    public function testAttachFilesReturnsFalseWhenNoAttachments(): void
    {
        $this->message->html('Test body');
        $result = $this->mailService->attachFiles();
        $this->assertFalse($result);
    }

    public function testAttachFilesCreatesFlatMixedPart(): void
    {
        $this->message->html('<p>Test</p>');

        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf'
        );
        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testXlsAttachment.xls'
        );

        $this->mailService->attachFiles();

        $body = $this->message->getBody();
        $this->assertInstanceOf(MixedPart::class, $body);

        $parts = $body->getParts();
        // 1 TextPart (html) + 2 DataParts (attachments) = 3 parts at same level
        $this->assertCount(3, $parts);
        $this->assertInstanceOf(TextPart::class, $parts[0]);
        $this->assertInstanceOf(DataPart::class, $parts[1]);
        $this->assertInstanceOf(DataPart::class, $parts[2]);
    }

    public function testAttachFilesSingleAttachmentIsFlat(): void
    {
        $this->message->html('<p>Single attachment</p>');

        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf'
        );

        $this->mailService->attachFiles();

        $body = $this->message->getBody();
        $this->assertInstanceOf(MixedPart::class, $body);

        $parts = $body->getParts();
        $this->assertCount(2, $parts);
        $this->assertInstanceOf(TextPart::class, $parts[0]);
        $this->assertInstanceOf(DataPart::class, $parts[1]);
    }

    public function testAttachFilesThrowsOnMissingFile(): void
    {
        $this->message->html('<p>Test</p>');

        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf'
        );
        $this->mailService->addAttachment('/nonexistent/file.pdf');

        $this->expectException(MailException::class);
        $this->expectExceptionMessage('Attachment "/nonexistent/file.pdf" does not exist or is not readable');
        $this->mailService->attachFiles();
    }

    public function testAttachFilesPreservesCustomFilename(): void
    {
        $this->message->html('<p>Test</p>');

        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf',
            'custom-ticket.pdf'
        );

        $this->mailService->attachFiles();

        $body = $this->message->getBody();
        $this->assertInstanceOf(MixedPart::class, $body);
        $parts = $body->getParts();

        $this->assertCount(2, $parts);
        $attachment = $parts[1];
        $this->assertInstanceOf(DataPart::class, $attachment);
        $this->assertSame('custom-ticket.pdf', $attachment->getFilename());
    }

    public function testAttachFilesWithTextPartBody(): void
    {
        $textPart = new TextPart('<h1>HTML content</h1>', 'utf-8', 'html');
        $this->mailService->setBody($textPart);

        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf'
        );
        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testXlsAttachment.xls'
        );

        $this->mailService->attachFiles();

        $body = $this->message->getBody();
        $this->assertInstanceOf(MixedPart::class, $body);

        $parts = $body->getParts();
        $this->assertCount(3, $parts);
        $this->assertInstanceOf(TextPart::class, $parts[0]);
        $this->assertInstanceOf(DataPart::class, $parts[1]);
        $this->assertInstanceOf(DataPart::class, $parts[2]);
    }

    public function testAttachFilesNoNestedMixedParts(): void
    {
        $this->message->html('<p>Test nesting</p>');

        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf'
        );
        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testXlsAttachment.xls'
        );

        $this->mailService->attachFiles();

        $body = $this->message->getBody();
        $this->assertInstanceOf(MixedPart::class, $body);
        $parts = $body->getParts();

        // None of the children should be a MixedPart (no nesting)
        foreach ($parts as $part) {
            $this->assertNotInstanceOf(MixedPart::class, $part);
        }
    }

    /**
     * Found an attachment leak where DataParts
     * embedded into Message::$body by attachFiles() survived a failed send
     * and were wrapped into the next email. After send() (success OR failure)
     * the service must hand out a clean message built from the configured defaults.
     */
    public function testMessageIsResetAfterFailedSendToPreventAttachmentLeak(): void
    {
        $this->mailService->setSubject('First');
        $this->message->html('First body');
        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf'
        );

        $this->transportInterface
            ->method('send')
            ->willThrowException(new RuntimeException('SMTP failure'));

        try {
            $this->mailService->send();
            $this->fail('Expected MailException was not thrown');
        } catch (MailException) {
            // expected
        }

        $this->assertNotSame($this->message, $this->mailService->getMessage());
        $this->assertNull(
            $this->readPrivateMessageBody($this->mailService->getMessage()),
            'After a failed send the message must be rebuilt from the defaults, without the attached body.'
        );
        $this->assertSame([], $this->mailService->getAttachments());
    }

    /**
     * End-to-end regression for the same leak: after a failed send with
     * attachments, configure a fresh email with no attachments, and verify
     * the message handed to the transport contains no DataPart from the
     * previous send.
     */
    public function testAttachmentsDoNotLeakIntoSubsequentSendAfterFailure(): void
    {
        $this->mailService->setSubject('First');
        $this->message->html('First email');
        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf'
        );

        $capturedSecondBody = null;
        $callCount          = 0;
        $this->transportInterface
            ->method('send')
            ->willReturnCallback(function ($email) use (&$capturedSecondBody, &$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    throw new RuntimeException('first send fails');
                }
                $capturedSecondBody = $email->getBody();
                return null;
            });

        try {
            $this->mailService->send();
        } catch (MailException) {
            // expected
        }

        // Configure a brand new email; the attachment of the first one must be gone.
        $this->mailService->setSubject('Second');
        $this->mailService->getMessage()->html('Second email - must not carry first email\'s attachment');

        $this->mailService->send();

        $this->assertInstanceOf(
            AbstractPart::class,
            $capturedSecondBody,
            'Transport did not receive a second message body'
        );
        $this->assertFalse(
            $this->bodyContainsDataPart($capturedSecondBody),
            'Second email leaked a DataPart from the failed first send'
        );
    }

    /**
     * Confirms the success path also resets the message, so a future refactor
     * does not silently regress the success case.
     */
    public function testMessageIsResetAfterSuccessfulSend(): void
    {
        $this->mailService->setSubject('Hello');
        $this->message->html('Body');
        $this->message->addTo('first@example.com');
        $this->mailService->addAttachment(
            $this->fileSystem->url() . '/data/mail/attachments/testPdfAttachment.pdf'
        );

        $this->transportInterface->expects($this->once())->method('send');

        $this->mailService->send();

        $message = $this->mailService->getMessage();
        $this->assertNotSame($this->message, $message);
        $this->assertNull($this->readPrivateMessageBody($message));
        $this->assertNull($message->getSubject());
        $this->assertSame([], $message->getTo());
        $this->assertSame([], $this->mailService->getAttachments());
    }

    public function testResetRestoresConfiguredDefaults(): void
    {
        $defaults = new Email();
        $defaults->subject('Default subject');
        $defaults->addFrom('from@example.com');

        $mailService = new MailService(
            $this->createMock(LogServiceInterface::class),
            $defaults,
            $this->transportInterface,
            $this->createMock(MailOptions::class),
            ['/default/attachment.txt']
        );

        $mailService->setSubject('Changed');
        $mailService->getMessage()->addTo('someone@example.com');
        $mailService->addAttachment('/extra/attachment.txt');

        $mailService->reset();

        $this->assertSame('Default subject', $mailService->getMessage()->getSubject());
        $this->assertSame('from@example.com', $mailService->getMessage()->getFrom()[0]->getAddress());
        $this->assertSame([], $mailService->getMessage()->getTo());
        $this->assertSame(['/default/attachment.txt'], $mailService->getAttachments());
    }

    public function testListenersSeeTheFullMessageBeforeTheReset(): void
    {
        $listener = new class extends AbstractMailEventListener {
            public array $seen = [];

            public function onPostSend(MailEvent $e): void
            {
                $this->seen[] = $e->getMailService()->getMessage()->getSubject();
            }
        };
        $this->mailService->attachListener($listener);
        $this->mailService->setSubject('Visible to listeners');

        $this->mailService->send();

        $this->assertSame(['Visible to listeners'], $listener->seen);
        $this->assertNull($this->mailService->getMessage()->getSubject());
    }

    public function testSendFailureKeepsThePreviousException(): void
    {
        $exception = new RuntimeException('Transport failure', 42);
        $this->transportInterface->method('send')->willThrowException($exception);

        try {
            $this->mailService->send();
            $this->fail('Expected MailException was not thrown');
        } catch (MailException $e) {
            $this->assertSame($exception, $e->getPrevious());
            $this->assertSame(42, $e->getCode());
            $this->assertSame('Transport failure', $e->getMessage());
        }
    }

    public function testErrorsAreReportedAsSendErrors(): void
    {
        $listener = new class extends AbstractMailEventListener {
            public array $events = [];

            public function onSendError(MailEvent $e): void
            {
                $this->events[] = $e->getResult()?->getException()::class;
            }
        };
        $this->mailService->attachListener($listener);
        $this->transportInterface->method('send')->willThrowException(new TypeError('Type problem'));

        try {
            $this->mailService->send();
            $this->fail('Expected MailException was not thrown');
        } catch (MailException $e) {
            $this->assertInstanceOf(TypeError::class, $e->getPrevious());
        }

        $this->assertSame([TypeError::class], $listener->events);
    }

    public function testFailingPostSendListenerIsNotReportedAsSendError(): void
    {
        $listener = new class extends AbstractMailEventListener {
            public int $errors = 0;

            public function onPostSend(MailEvent $e): void
            {
                throw new RuntimeException('Listener failure');
            }

            public function onSendError(MailEvent $e): void
            {
                $this->errors++;
            }
        };
        $this->mailService->attachListener($listener);
        $this->mailService->setSubject('Delivered');

        $caught = null;
        try {
            $this->mailService->send();
        } catch (Throwable $e) {
            $caught = $e;
        }

        $this->assertInstanceOf(RuntimeException::class, $caught);
        $this->assertSame('Listener failure', $caught->getMessage());
        $this->assertSame(0, $listener->errors);
        $this->assertNull($this->mailService->getMessage()->getSubject(), 'The message must be reset anyway');
    }

    public function testDirectoryAttachmentsAreCollectedAtSendTime(): void
    {
        $dir = $this->fileSystem->url() . '/data/mail/attachments';
        file_put_contents($dir . '/.gitkeep', '');
        mkdir($dir . '/nested');
        file_put_contents($dir . '/nested/nested.txt', 'nested');

        $options = new MailOptions([
            'message_options' => [
                'attachments' => ['dir' => ['iterate' => true, 'path' => $dir, 'recursive' => false]],
            ],
        ]);

        $message     = new Email();
        $mailService = new MailService(
            $this->createMock(LogServiceInterface::class),
            $message,
            $this->transportInterface,
            $options
        );
        $message->html('<p>Test</p>');

        // added after the service was created
        file_put_contents($dir . '/late.txt', 'late');

        $mailService->attachFiles();

        $body = $message->getBody();
        $this->assertInstanceOf(MixedPart::class, $body);
        $names = [];
        foreach ($body->getParts() as $part) {
            if ($part instanceof DataPart) {
                $names[] = $part->getFilename();
            }
        }
        sort($names);

        $this->assertSame(['late.txt', 'testPdfAttachment.pdf', 'testXlsAttachment.xls'], $names);
    }

    public function testRecursiveDirectoryAttachments(): void
    {
        $dir = $this->fileSystem->url() . '/data/mail/attachments';
        mkdir($dir . '/nested');
        file_put_contents($dir . '/nested/nested.txt', 'nested');

        $options = new MailOptions([
            'message_options' => [
                'attachments' => ['dir' => ['iterate' => true, 'path' => $dir, 'recursive' => true]],
            ],
        ]);

        $message     = new Email();
        $mailService = new MailService(
            $this->createMock(LogServiceInterface::class),
            $message,
            $this->transportInterface,
            $options
        );
        $message->html('<p>Test</p>');

        $mailService->attachFiles();

        $body = $message->getBody();
        $this->assertInstanceOf(MixedPart::class, $body);
        $this->assertCount(4, $body->getParts());
    }

    private function readPrivateMessageBody(Message $message): ?AbstractPart
    {
        $reflection = new ReflectionClass(Message::class);
        $property   = $reflection->getProperty('body');

        return $property->getValue($message);
    }

    private function bodyContainsDataPart(AbstractPart $part): bool
    {
        if ($part instanceof DataPart) {
            return true;
        }

        if ($part instanceof MixedPart) {
            foreach ($part->getParts() as $child) {
                if ($this->bodyContainsDataPart($child)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function testSetBodyUsesProvidedCharset(): void
    {
        $this->mailService->setBody('<p>Body</p>', 'iso-8859-1');

        $this->assertSame('iso-8859-1', $this->message->getHtmlCharset());
    }

    public function testSetBodyDefaultsToUtf8(): void
    {
        $this->mailService->setBody('<p>Body</p>');

        $this->assertSame('utf-8', $this->message->getHtmlCharset());
    }

    public function testSendKeepsConfiguredEncoding(): void
    {
        $encoding = null;
        $this->transportInterface->method('send')->willReturnCallback(
            function (Email $email) use (&$encoding) {
                $encoding = $email->getEncoding();
                return null;
            }
        );

        $this->mailService->setBody('<p>Body</p>', 'iso-8859-1');
        $this->mailService->send();

        $this->assertSame('iso-8859-1', $encoding);
    }

    public function testSendSetsDefaultEncodingWhenNoneIsSet(): void
    {
        $encoding = 'not called';
        $this->transportInterface->method('send')->willReturnCallback(
            function (Email $email) use (&$encoding) {
                $encoding = $email->getEncoding();
                return null;
            }
        );

        $this->assertNull($this->message->getEncoding());

        $this->mailService->send();

        $this->assertSame('utf-8', $encoding);
    }

    /**
     * @throws Exception
     */
    public function testLogFailureDoesNotBreakSuccessfulSend(): void
    {
        $logService = $this->createMock(LogServiceInterface::class);
        $logService->expects($this->once())
            ->method('sent')
            ->willThrowException(new RuntimeException('Log failure'));

        $mailService = new MailService(
            $logService,
            new Email(),
            $this->transportInterface,
            $this->createMock(MailOptions::class)
        );

        $result = $mailService->send();

        $this->assertTrue($result->isValid());
    }
}
