<?php

declare(strict_types=1);

namespace DotTest\Mail;

use Dot\Mail\ConfigProvider;
use Dot\Mail\Factory\LogServiceFactory;
use Dot\Mail\Factory\MailOptionsAbstractFactory;
use Dot\Mail\Factory\MailServiceAbstractFactory;
use Dot\Mail\Service\LogService;
use Dot\Mail\Service\LogServiceInterface;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    private array $config;

    public function setUp(): void
    {
        $this->config = (new ConfigProvider())();
    }

    public function testHasDependencies(): void
    {
        $this->assertArrayHasKey('dependencies', $this->config);
    }

    public function testDependenciesHasFactories(): void
    {
        $this->assertArrayHasKey('factories', $this->config['dependencies']);
        $this->assertSame(
            LogServiceFactory::class,
            $this->config['dependencies']['factories'][LogService::class]
        );
    }

    public function testDependenciesHasAliases(): void
    {
        $this->assertArrayHasKey('aliases', $this->config['dependencies']);
        $this->assertSame(
            LogService::class,
            $this->config['dependencies']['aliases'][LogServiceInterface::class]
        );
    }

    public function testDependenciesHasAbstractFactories(): void
    {
        $this->assertArrayHasKey('abstract_factories', $this->config['dependencies']);
        $this->assertContains(
            MailServiceAbstractFactory::class,
            $this->config['dependencies']['abstract_factories']
        );
        $this->assertContains(
            MailOptionsAbstractFactory::class,
            $this->config['dependencies']['abstract_factories']
        );
    }
}
