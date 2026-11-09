<?php

declare(strict_types=1);

namespace DotTest\Mail\Integration;

use Dot\Mail\ConfigProvider;
use Dot\Mail\Factory\LogServiceFactory;
use Dot\Mail\Factory\MailOptionsFactory;
use Dot\Mail\Factory\MailServiceFactory;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Service\MailServiceInterface;
use DotTest\Mail\Fixture\InMemoryContainer;
use DotTest\Mail\Fixture\RecordingTransport;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Mime\Part\TextPart;

use function dirname;
use function file_get_contents;
use function is_dir;
use function is_file;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * Wires the package ConfigProvider into a plain PSR-11 container, the way any container consumes it:
 * every factory is invoked with the container and the requested service name, every alias resolves to its target.
 */
class ConfigProviderWiringTest extends TestCase
{
    private string $logFile;
    private RecordingTransport $transport;
    private InMemoryContainer $container;

    public function setUp(): void
    {
        $this->logFile   = sys_get_temp_dir() . '/dot-mail-integration-' . uniqid() . '/sent.log';
        $this->transport = new RecordingTransport();
        $this->container = new InMemoryContainer();

        $dependencies = (new ConfigProvider())()['dependencies'];
        foreach ($dependencies['factories'] as $name => $factory) {
            $this->registerFactory($name, $factory);
        }
        foreach ($dependencies['aliases'] as $alias => $target) {
            $this->container->set(
                $alias,
                static fn (ContainerInterface $container): mixed => $container->get($target)
            );
        }

        // an application registers every mail service other than "default" itself
        $this->registerFactory('dot-mail.service.newsletter', MailServiceFactory::class);
        $this->container->set('mail.transport', $this->transport);
        $this->container->set('config', [
            'dot_mail' => [
                'log'      => ['sent' => $this->logFile],
                'services' => [
                    'default'    => [
                        'transport'       => 'mail.transport',
                        'message_options' => ['from' => 'from@example.com'],
                    ],
                    'newsletter' => [
                        'transport'       => 'mail.transport',
                        'message_options' => ['from' => 'news@example.com'],
                    ],
                ],
            ],
        ]);
    }

    public function tearDown(): void
    {
        if (is_file($this->logFile)) {
            unlink($this->logFile);
        }
        if (is_dir(dirname($this->logFile))) {
            rmdir(dirname($this->logFile));
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDefaultServiceSendsAndLogs(): void
    {
        $mailService = $this->container->get(MailServiceInterface::class);
        $this->assertInstanceOf(MailServiceInterface::class, $mailService);
        $this->assertSame($mailService, $this->container->get('dot-mail.service.default'));

        $mailService->setBody('Email body');
        $mailService->setSubject('Email subject');
        $mailService->getMessage()->addTo('email@example.com', 'User name');

        $this->assertTrue($mailService->send()->isValid());
        $this->assertCount(1, $this->transport->sent);
        $this->assertStringContainsString(
            '{"subject":"Email subject","to":["User name <email@example.com>"],"cc":[],"bcc":[]}',
            (string) file_get_contents($this->logFile)
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testNamedServiceIsRegisteredWithTheFactory(): void
    {
        $newsletter = $this->container->get('dot-mail.service.newsletter');
        $default    = $this->container->get('dot-mail.service.default');

        $this->assertInstanceOf(MailServiceInterface::class, $newsletter);
        $this->assertInstanceOf(MailServiceInterface::class, $default);
        $this->assertNotSame($default, $newsletter);
        $this->assertSame('news@example.com', $newsletter->getMessage()->getFrom()[0]->getAddress());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDefaultOptionsAreRegistered(): void
    {
        $options = $this->container->get('dot-mail.options.default');

        $this->assertInstanceOf(MailOptions::class, $options);
        $this->assertSame('from@example.com', $options->getMessageOptions()->getFrom());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMimePartBodyIsSent(): void
    {
        $mailService = $this->container->get(MailServiceInterface::class);
        $this->assertInstanceOf(MailServiceInterface::class, $mailService);

        $mailService->getMessage()->addTo('email@example.com');
        $mailService->setBody(new TextPart('plain body'));

        $this->assertTrue($mailService->send()->isValid());
        $this->assertCount(1, $this->transport->sent);
    }

    /**
     * @param class-string<LogServiceFactory|MailServiceFactory|MailOptionsFactory> $factory
     */
    private function registerFactory(string $name, string $factory): void
    {
        $this->container->set(
            $name,
            static function (ContainerInterface $container) use ($factory, $name): object {
                $instance = new $factory();

                return $instance instanceof LogServiceFactory
                    ? $instance($container)
                    : $instance($container, $name);
            }
        );
    }
}
