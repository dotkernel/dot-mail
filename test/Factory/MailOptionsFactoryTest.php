<?php

declare(strict_types=1);

namespace DotTest\Mail\Factory;

use Dot\Mail\Exception\InvalidArgumentException;
use Dot\Mail\Factory\MailOptionsFactory;
use Dot\Mail\Options\MailOptions;
use DotTest\Mail\CommonTrait;
use DotTest\Mail\Fixture\InMemoryContainer;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MailOptionsFactoryTest extends TestCase
{
    use CommonTrait;

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

        $this->config = $this->generateConfig();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGeneratesMailOptions(): void
    {
        $defaultName = 'dot-mail.options.default';
        $container   = new InMemoryContainer(['config' => $this->config]);

        $subject = (new MailOptionsFactory())($container, $defaultName);

        $this->assertContainsOnlyInstancesOf(MailOptions::class, [$subject]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testNonArrayServiceConfigThrows(): void
    {
        $container = new InMemoryContainer(['config' => $this->config]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mail service "test" is not configured under "dot_mail.services"');
        (new MailOptionsFactory())($container, 'dot-mail.options.test');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUnconfiguredServiceThrows(): void
    {
        $container = new InMemoryContainer(['config' => $this->config]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mail service "missing" is not configured under "dot_mail.services"');
        (new MailOptionsFactory())($container, 'dot-mail.options.missing');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDefaultServiceIsUsedWithoutARequestedName(): void
    {
        $container = new InMemoryContainer([
            'config' => [
                'dot_mail' => ['services' => ['default' => ['transport' => 'esmtp']]],
            ],
        ]);

        $this->assertSame(
            EsmtpTransport::class,
            (new MailOptionsFactory())($container)->getTransport()
        );
    }
}
