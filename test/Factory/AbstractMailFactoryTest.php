<?php

declare(strict_types=1);

namespace DotTest\Mail\Factory;

use Dot\Mail\Factory\AbstractMailFactory;
use Dot\Mail\Options\MailOptions;
use DotTest\Mail\CommonTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

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
        $container->expects(self::once())->method('get')->willReturn($this->config);

        $result = $this->subject->canCreate($container, $requestedName);
        $this->assertTrue($result);
    }
}
