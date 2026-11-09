<?php

declare(strict_types=1);

namespace Dot\Mail;

use Dot\Mail\Factory\LogServiceFactory;
use Dot\Mail\Factory\MailOptionsFactory;
use Dot\Mail\Factory\MailServiceFactory;
use Dot\Mail\Service\LogService;
use Dot\Mail\Service\LogServiceInterface;
use Dot\Mail\Service\MailServiceInterface;

/**
 * @phpstan-type DependencyConfig array{
 *     factories: array<string, class-string<LogServiceFactory|MailServiceFactory|MailOptionsFactory>>,
 *     aliases: array<string, string>
 * }
 */
class ConfigProvider
{
    /**
     * @return array{dependencies: DependencyConfig}
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Registers the default mail service. Register other mail services the same way, for example
     * 'dot-mail.service.newsletter' => MailServiceFactory::class
     *
     * @return DependencyConfig
     */
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                LogService::class          => LogServiceFactory::class,
                'dot-mail.service.default' => MailServiceFactory::class,
                'dot-mail.options.default' => MailOptionsFactory::class,
            ],
            'aliases'   => [
                LogServiceInterface::class  => LogService::class,
                MailServiceInterface::class => 'dot-mail.service.default',
            ],
        ];
    }
}
