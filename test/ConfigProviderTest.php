<?php

declare(strict_types=1);

namespace DotTest\Mail;

use Dot\Mail\ConfigProvider;
use Dot\Mail\Factory\LogServiceFactory;
use Dot\Mail\Factory\MailOptionsFactory;
use Dot\Mail\Factory\MailServiceFactory;
use Dot\Mail\Service\LogService;
use Dot\Mail\Service\LogServiceInterface;
use Dot\Mail\Service\MailServiceInterface;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /** @var array<string, mixed> */
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
        $this->assertSame(
            'dot-mail.service.default',
            $this->config['dependencies']['aliases'][MailServiceInterface::class]
        );
    }

    public function testDefaultMailServiceIsRegistered(): void
    {
        $factories = $this->config['dependencies']['factories'];

        $this->assertSame(MailServiceFactory::class, $factories['dot-mail.service.default']);
        $this->assertSame(MailOptionsFactory::class, $factories['dot-mail.options.default']);
    }

    public function testNoAbstractFactoriesAreRegistered(): void
    {
        $this->assertArrayNotHasKey('abstract_factories', $this->config['dependencies']);
    }
}
