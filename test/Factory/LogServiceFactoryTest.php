<?php

declare(strict_types=1);

namespace DotTest\Mail\Factory;

use Dot\Mail\Email;
use Dot\Mail\Factory\LogServiceFactory;
use Dot\Mail\Service\LogService;
use DotTest\Mail\Fixture\InMemoryContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function dirname;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class LogServiceFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testServiceCreatedWithoutValidConfig(): void
    {
        $container  = new InMemoryContainer(['config' => ['test' => 'invalid data']]);
        $logService = (new LogServiceFactory())($container);

        $this->assertInstanceOf(LogService::class, $logService);
        // if no valid config, logging will be disabled
        $this->assertNull($logService->sent(new Email()));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testServiceUsesTheDotMailLogTarget(): void
    {
        $target     = sys_get_temp_dir() . '/dot-mail-test-' . uniqid() . '/sent.log';
        $container  = new InMemoryContainer(['config' => ['dot_mail' => ['log' => ['sent' => $target]]]]);
        $logService = (new LogServiceFactory())($container);

        $message = (new Email())->subject('Logged');
        $this->assertIsInt($logService->sent($message));
        $this->assertFileExists($target);

        unlink($target);
        rmdir(dirname($target));
    }
}
