<?php

declare(strict_types=1);

namespace DotTest\Mail\Factory;

use Dot\Mail\Factory\AbstractMailFactory;
use Dot\Mail\Options\MailOptions;
use DotTest\Mail\CommonTrait;
use ErrorException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function restore_error_handler;
use function set_error_handler;

class AbstractMailFactoryTest extends TestCase
{
    use CommonTrait;

    private AbstractMailFactory $subject;

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
        $this->subject = new class extends AbstractMailFactory {
            public const SPECIFIC_PART = 'testPart';

            /**
             * @param string $requestedName
             */
            public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MailOptions
            {
                return new MailOptions();
            }
        };
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testCanCreateRequestedService(): void
    {
        $container     = $this->createMock(ContainerInterface::class);
        $requestedName = 'dot-mail.testPart.default';
        $container->expects($this->once())->method('get')->willReturn($this->config);

        $result = $this->subject->canCreate($container, $requestedName);
        $this->assertTrue($result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testWillNotCreateRequestedNameFromWrongParts(): void
    {
        $container     = $this->createMock(ContainerInterface::class);
        $requestedName = 'dot-mail.testPart.default.test';

        $result = $this->subject->canCreate($container, $requestedName);
        $this->assertFalse($result);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWillNotCreateRequestedNameWithoutParts(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        set_error_handler(static function (int $severity, string $message): never {
            throw new ErrorException($message, 0, $severity);
        });

        try {
            $this->assertFalse($this->subject->canCreate($container, 'dot-mail'));
            $this->assertFalse($this->subject->canCreate($container, 'dot-mail.testPart'));
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLogKeyIsNotACreatableService(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn($this->config);

        $this->assertTrue($this->subject->canCreate($container, 'dot-mail.testPart.default'));
        $this->assertFalse($this->subject->canCreate($container, 'dot-mail.testPart.log'));
    }
}
