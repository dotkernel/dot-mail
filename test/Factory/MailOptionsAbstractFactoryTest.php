<?php

declare(strict_types=1);

namespace DotTest\Mail\Factory;

use Dot\Mail\Factory\MailOptionsAbstractFactory;
use Dot\Mail\Options\MailOptions;
use DotTest\Mail\CommonTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MailOptionsAbstractFactoryTest extends TestCase
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
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testGeneratesMailOptions(): void
    {
        $defaultName = 'dot-mail.options.default';
        $container   = $this->createMock(ContainerInterface::class);

        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($this->config);

        $subject = (new MailOptionsAbstractFactory())($container, $defaultName);

        $this->assertInstanceOf(MailOptions::class, $subject);
    }
}
