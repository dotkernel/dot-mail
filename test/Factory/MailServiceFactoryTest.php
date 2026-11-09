<?php

declare(strict_types=1);

namespace DotTest\Mail\Factory;

use Dot\Mail\Event\AbstractMailEventListener;
use Dot\Mail\Event\MailEvent;
use Dot\Mail\Exception\InvalidArgumentException;
use Dot\Mail\Exception\RuntimeException;
use Dot\Mail\Factory\MailServiceFactory as Subject;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Service\LogService;
use Dot\Mail\Service\LogServiceInterface;
use Dot\Mail\Service\MailService;
use DotTest\Mail\CommonTrait;
use DotTest\Mail\Fixture\InMemoryContainer;
use ErrorException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

use function restore_error_handler;
use function set_error_handler;

class MailServiceFactoryTest extends TestCase
{
    use CommonTrait;

    private Subject $subject;

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

        $this->config  = $this->generateConfig();
        $this->subject = new Subject();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGenerateServiceWithProvidedAdapter(): void
    {
        $provided = new EsmtpTransport();
        $options  = [
            'transport'       => $provided,
            'event_listeners' => [AbstractMailEventListener::class],
            'message_options' => [
                'attachments' => [
                    'files' => ['testPdfAttachment.pdf', 'testXlsAttachment.xls'],
                    'dir'   => [
                        'iterate'   => true,
                        'path'      => $this->fileSystem->url() . '/data/mail/attachments',
                        'recursive' => true,
                    ],
                ],
            ],
        ];

        $container = $this->containerFor($options, [
            AbstractMailEventListener::class => new class extends AbstractMailEventListener {
            },
        ]);

        $mailService = ($this->subject)($container, 'dot-mail.service.default');
        $this->assertInstanceOf(MailService::class, $mailService);
        $this->assertSame($provided, $mailService->getTransport());
        $this->assertSame(['testPdfAttachment.pdf', 'testXlsAttachment.xls'], $mailService->getAttachments());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testTransportServiceFromTheContainerIsUsed(): void
    {
        $service   = new EsmtpTransport('service.example.com', 25, false);
        $options   = ['transport' => 'mail.transport'];
        $container = $this->containerFor($options, ['mail.transport' => $service]);

        $this->assertSame($service, ($this->subject)($container, 'dot-mail.service.default')->getTransport());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testInvalidListenerServiceThrows(): void
    {
        $options   = [
            'transport'       => 'sendmail',
            'event_listeners' => ['Invalid Listener Test'],
        ];
        $container = $this->containerFor($options, ['Invalid Listener Test' => 'Invalid Listener provided']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Mail event listener "Invalid Listener Test" must be an instance of');
        ($this->subject)($container, 'dot-mail.service.default');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testInvalidListenerConfigThrows(): void
    {
        $listener = new class extends AbstractMailEventListener {
        };
        $cases    = [
            'integer type'    => [['type' => 123]],
            'missing type'    => [['priority' => 2]],
            'string priority' => [['type' => 'listener', 'priority' => 'high']],
            'not a string'    => [42],
        ];

        foreach ($cases as $label => $listeners) {
            $options   = ['transport' => 'sendmail', 'event_listeners' => $listeners];
            $container = $this->containerFor($options, ['listener' => $listener]);

            try {
                ($this->subject)($container, 'dot-mail.service.default');
                $this->fail('Expected RuntimeException for ' . $label);
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('Mail event listener config must be', $e->getMessage(), $label);
            }
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testListenerConfigWithPriorityIsAttached(): void
    {
        $listener  = new class extends AbstractMailEventListener {
            public int $preSend = 0;

            public function onPreSend(MailEvent $e): void
            {
                $this->preSend++;
            }
        };
        $options   = [
            'transport'       => NullTransport::class,
            'event_listeners' => [['type' => 'listener', 'priority' => 5], 'listener'],
            'message_options' => ['from' => 'from@example.com', 'to' => ['to@example.com']],
        ];
        $container = $this->containerFor($options, ['listener' => $listener]);

        $mailService = ($this->subject)($container, 'dot-mail.service.default');
        $mailService->setBody('<p>Body</p>');
        $mailService->send();

        $this->assertSame(2, $listener->preSend);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGenerateServiceInvalidAdapter(): void
    {
        $container = $this->containerFor(['transport' => 'test']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/"transport" must be a known alias.*"test" provided/');
        ($this->subject)($container, 'dot-mail.service.default');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testTransportServiceThatIsNotATransportThrows(): void
    {
        $options   = ['transport' => 'not.a.transport'];
        $container = $this->containerFor($options, ['not.a.transport' => new MailOptions()]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided transport service does not return a');
        ($this->subject)($container, 'dot-mail.service.default');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGenerateServiceFromMinimalConfiguration(): void
    {
        $container = $this->containerFor([]);

        $mailService = ($this->subject)($container, 'dot-mail.service.default');

        $this->assertInstanceOf(MailService::class, $mailService);
        $this->assertInstanceOf(SendmailTransport::class, $mailService->getTransport());
        $this->assertNull($mailService->getMessage()->getHtmlBody());
        $this->assertSame([], $mailService->getAttachments());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGenerateServiceWithBodyContent(): void
    {
        $options = [
            'transport'       => 'sendmail',
            'message_options' => ['body' => ['content' => '<p>Hello</p>', 'charset' => 'iso-8859-1']],
        ];

        $container = $this->containerFor($options);

        $mailService = ($this->subject)($container, 'dot-mail.service.default');

        $this->assertSame('<p>Hello</p>', $mailService->getMessage()->getHtmlBody());
        $this->assertSame('iso-8859-1', $mailService->getMessage()->getHtmlCharset());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testEsmtpTransportWithoutConnectionConfigKeys(): void
    {
        $options = [
            'transport'    => 'esmtp',
            'smtp_options' => ['host' => 'localhost', 'port' => 587],
        ];

        $container = $this->containerFor($options);

        set_error_handler(static function (int $severity, string $message): never {
            throw new ErrorException($message, 0, $severity);
        });

        try {
            $transport = ($this->subject)($container, 'dot-mail.service.default')->getTransport();
        } finally {
            restore_error_handler();
        }

        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertSame('', $transport->getUsername());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testEsmtpCredentialsWithSpecialCharacters(): void
    {
        $options = [
            'transport'    => 'esmtp',
            'smtp_options' => [
                'host'              => 'localhost',
                'port'              => 587,
                'connection_config' => ['username' => 'user@example.com', 'password' => 'p:ss/w@rd'],
            ],
        ];

        $container = $this->containerFor($options);

        $transport = ($this->subject)($container, 'dot-mail.service.default')->getTransport();

        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertSame('user@example.com', $transport->getUsername());
        $this->assertSame('p:ss/w@rd', $transport->getPassword());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testEsmtpTransportConfiguration(): void
    {
        $options = [
            'transport'    => 'esmtp',
            'smtp_options' => [
                'host'                  => 'mail.example.com',
                'port'                  => 2525,
                'name'                  => 'client.example.com',
                'connection_time_limit' => 7,
                'connection_config'     => ['username' => 'user', 'password' => 'secret'],
            ],
        ];

        $transport = ($this->subject)($this->containerFor($options), 'dot-mail.service.default')->getTransport();

        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertSame('client.example.com', $transport->getLocalDomain());
        $stream = $transport->getStream();
        $this->assertInstanceOf(SocketStream::class, $stream);
        $this->assertSame(7.0, $stream->getTimeout());
        $this->assertTrue($transport->isAutoTls());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testEsmtpTlsSettings(): void
    {
        $cases = [
            'automatic on 587' => [587, null, true, false],
            'implicit on 465'  => [465, null, true, true],
            'disabled'         => [587, false, false, false],
        ];

        foreach ($cases as $label => [$port, $tls, $autoTls, $implicitTls]) {
            $options = [
                'transport'    => 'esmtp',
                'smtp_options' => [
                    'host'              => 'mail.example.com',
                    'port'              => $port,
                    'connection_config' => ['tls' => $tls],
                ],
            ];

            $transport = ($this->subject)($this->containerFor($options), 'dot-mail.service.default')->getTransport();

            $this->assertInstanceOf(EsmtpTransport::class, $transport);
            $this->assertSame($autoTls, $transport->isAutoTls(), $label);
            $this->assertInstanceOf(SocketStream::class, $transport->getStream());
            $this->assertSame($implicitTls, $transport->getStream()->isTLS(), $label);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testProvidedTransportObjectIsUsedAsGiven(): void
    {
        $provided = new EsmtpTransport('provided.example.com', 25, false);
        $options  = [
            'transport'    => $provided,
            'smtp_options' => ['host' => 'ignored.example.com', 'port' => 587],
        ];

        $transport = ($this->subject)($this->containerFor($options), 'dot-mail.service.default')->getTransport();

        $this->assertSame($provided, $transport);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testServiceKeepsConfiguredDefaultsAfterReset(): void
    {
        $options = [
            'transport'       => 'sendmail',
            'message_options' => [
                'from'        => 'from@example.com',
                'subject'     => 'Default subject',
                'body'        => ['content' => '<p>Default</p>'],
                'attachments' => ['files' => ['/some/default/file.txt']],
            ],
        ];

        $mailService = ($this->subject)($this->containerFor($options), 'dot-mail.service.default');
        $mailService->getMessage()->addTo('someone@example.com');
        $mailService->addAttachment('/another/file.txt');

        $mailService->reset();

        $this->assertSame('Default subject', $mailService->getMessage()->getSubject());
        $this->assertSame('<p>Default</p>', $mailService->getMessage()->getHtmlBody());
        $this->assertSame('from@example.com', $mailService->getMessage()->getFrom()[0]->getAddress());
        $this->assertSame([], $mailService->getMessage()->getTo());
        $this->assertSame(['/some/default/file.txt'], $mailService->getAttachments());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUnconfiguredServiceThrows(): void
    {
        $container = $this->containerFor(['transport' => 'sendmail']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mail service "missing" is not configured under "dot_mail.services"');
        ($this->subject)($container, 'dot-mail.service.missing');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDefaultServiceIsCreatedWithoutARequestedName(): void
    {
        $container = $this->containerFor([
            'transport'       => 'sendmail',
            'message_options' => ['from' => 'default@example.com'],
        ]);

        $mailService = ($this->subject)($container);

        $this->assertSame('default@example.com', $mailService->getMessage()->getFrom()[0]->getAddress());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testNamedServiceUsesItsOwnConfiguration(): void
    {
        $container = new InMemoryContainer([
            'config'                   => [
                'dot_mail' => [
                    'services' => [
                        'default'    => ['transport' => 'sendmail', 'message_options' => ['from' => 'one@example.com']],
                        'newsletter' => ['transport' => 'esmtp', 'message_options' => ['from' => 'news@example.com']],
                    ],
                ],
            ],
            LogServiceInterface::class => new LogService([]),
        ]);

        $newsletter = ($this->subject)($container, 'dot-mail.service.newsletter');

        $this->assertSame('news@example.com', $newsletter->getMessage()->getFrom()[0]->getAddress());
        $this->assertInstanceOf(EsmtpTransport::class, $newsletter->getTransport());
    }

    /**
     * @param array<string, mixed> $serviceConfig the dot_mail.services.default configuration
     * @param array<string, mixed> $services
     */
    private function containerFor(array $serviceConfig, array $services = []): ContainerInterface
    {
        return new InMemoryContainer([
            'config'                   => ['dot_mail' => ['services' => ['default' => $serviceConfig]]],
            LogServiceInterface::class => new LogService([]),
        ] + $services);
    }
}
