<?php

declare(strict_types=1);

namespace DotTest\Mail\Factory;

use Dot\Mail\Event\AbstractMailEventListener;
use Dot\Mail\Exception\RuntimeException;
use Dot\Mail\Factory\MailServiceAbstractFactory as Subject;
use Dot\Mail\Options\AttachmentsOptions;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Options\MessageOptions;
use Dot\Mail\Options\SmtpOptions;
use Dot\Mail\Service\LogService;
use Dot\Mail\Service\LogServiceInterface;
use Dot\Mail\Service\MailService;
use DotTest\Mail\CommonTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

class MailServiceAbstractFactoryTest extends TestCase
{
    use CommonTrait;

    private ContainerInterface|MockObject $container;
    private MailOptions|MockObject $mailOptions;
    private SmtpOptions|MockObject $smtpOptions;
    private MessageOptions|MockObject $messageOptions;
    private AttachmentsOptions|MockObject $attachmentsOptions;
    private Subject $subject;

    /**
     * @throws Exception
     */
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

        $this->container          = $this->createMock(ContainerInterface::class);
        $this->mailOptions        = $this->createMock(MailOptions::class);
        $this->smtpOptions        = $this->createMock(SmtpOptions::class);
        $this->messageOptions     = $this->createMock(MessageOptions::class);
        $this->attachmentsOptions = $this->createMock(AttachmentsOptions::class);

        $this->config  = $this->generateConfig();
        $this->subject = new Subject();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testGenerateServiceWithProvidedAdapter(): void
    {
        $requestedName = 'dot-mail.service.default';

        $this->attachmentsOptions->expects($this->any())
            ->method('getFiles')
            ->willReturn(['testPdfAttachment.pdf', 'testXlsAttachment.xls']);
        $this->attachmentsOptions->expects($this->any())
            ->method('getDir')
            ->willReturn([
                'iterate'   => true,
                'path'      => $this->fileSystem->url() . '/data/mail/attachments',
                'recursive' => true,
            ]);
        $this->messageOptions->expects($this->any())
            ->method('getAttachments')
            ->willReturn($this->attachmentsOptions);
        $this->mailOptions->expects($this->any())
            ->method('getMessageOptions')
            ->willReturn($this->messageOptions);
        $this->mailOptions->expects($this->any())
            ->method('getTransport')
            ->willReturn($this->createMock(SmtpTransport::class));
        $this->mailOptions->expects($this->any())
            ->method('getEventListeners')
            ->willReturn([AbstractMailEventListener::class]);

        $this->container->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['dot-mail.options.default', $this->mailOptions],
                [LogServiceInterface::class, $this->createMock(LogService::class)],
                [
                    AbstractMailEventListener::class,
                    new class extends AbstractMailEventListener {
                    },
                ],
            ]);
        $this->container->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $this->smtpOptions->expects($this->any())
            ->method('getConnectionConfig')
            ->willReturn([
                'username' => 'test',
                'password' => 'testPassword',
                'port'     => 587,
            ]);

        $this->smtpOptions->expects($this->once())
            ->method('getHost')
            ->willReturn('localhost');

        $this->mailOptions->expects($this->any())
            ->method('getSmtpOptions')
            ->willReturn($this->smtpOptions);

        $mailService = (new Subject())($this->container, $requestedName);
        $this->assertInstanceOf(MailService::class, $mailService);
        $this->assertInstanceOf(SmtpTransport::class, $mailService->getTransport());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testGenerateServiceWithoutProvidedAdapter(): void
    {
        $requestedName = 'dot-mail.service.default';

        $this->attachmentsOptions->expects($this->any())
            ->method('getFiles')
            ->willReturn(['testPdfAttachment.pdf', 'testXlsAttachment.xls']);
        $this->attachmentsOptions->expects($this->any())
            ->method('getDir')
            ->willReturn([
                'iterate'   => AttachmentsOptions::DEFAULT_ITERATE,
                'path'      => AttachmentsOptions::DEFAULT_PATH,
                'recursive' => AttachmentsOptions::DEFAULT_RECURSIVE,
            ]);
        $this->messageOptions->expects($this->any())
            ->method('getAttachments')
            ->willReturn($this->attachmentsOptions);
        $this->mailOptions->expects($this->any())
            ->method('getMessageOptions')
            ->willReturn($this->messageOptions);
        $this->mailOptions->expects($this->any())
            ->method('getTransport')
            ->willReturn(SmtpTransport::class);
        $this->mailOptions->expects($this->any())
            ->method('getEventListeners')
            ->willReturn(['Invalid Listener Test']);

        $this->container->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['dot-mail.options.default', $this->mailOptions],
                [LogServiceInterface::class, $this->createMock(LogService::class)],
                [SmtpTransport::class, new SmtpTransport()],
                ['Invalid Listener Test', 'Invalid Listener provided'],
            ]);
        $this->container->expects($this->any())
            ->method('has')
            ->willReturnMap([
                [SmtpTransport::class, true],
                ['Invalid Listener Test', true],
            ]);

        $this->smtpOptions->expects($this->any())
            ->method('getConnectionConfig')
            ->willReturn([
                'username' => 'test',
                'password' => 'testPassword',
                'port'     => 587,
            ]);

        $this->smtpOptions->expects($this->once())
            ->method('getHost')
            ->willReturn('localhost');

        $this->mailOptions->expects($this->any())
            ->method('getSmtpOptions')
            ->willReturn($this->smtpOptions);

        $this->expectException(RuntimeException::class);

        $mailService = (new Subject())($this->container, $requestedName);

        $this->assertInstanceOf(MailService::class, $mailService);
        $this->assertInstanceOf(SmtpTransport::class, $mailService->getTransport());
        $this->assertCount(2, $mailService->getAttachments());
    }
}
