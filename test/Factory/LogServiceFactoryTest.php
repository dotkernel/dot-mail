<?php

declare(strict_types=1);

namespace DotTest\Mail\Factory;

use Dot\Mail\Email;
use Dot\Mail\Factory\LogServiceFactory;
use Dot\Mail\Service\LogService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class LogServiceFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function testServiceCreatedWithoutValidConfig(): void
    {
        $message    = $this->createMock(Email::class);
        $container  = $this->createMock(ContainerInterface::class);
        $logService = (new LogServiceFactory())($container);

        $container->method('get')->with('config')->willReturn(['test' => 'invalid data']);

        $this->assertInstanceOf(LogService::class, $logService);
        // if no valid config, logging will be disabled
        $this->assertNull($logService->sent($message));
    }
}
