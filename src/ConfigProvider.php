<?php

declare(strict_types=1);

namespace Dot\Mail;

use Dot\Mail\Factory\LogServiceFactory;
use Dot\Mail\Factory\MailOptionsAbstractFactory;
use Dot\Mail\Factory\MailServiceAbstractFactory;
use Dot\Mail\Service\LogService;
use Dot\Mail\Service\LogServiceInterface;
use Dot\Mail\Service\MailServiceInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getDependencyConfig(): array
    {
        return [
            'factories'          => [
                LogService::class => LogServiceFactory::class,
            ],
            'aliases'            => [
                LogServiceInterface::class  => LogService::class,
                MailServiceInterface::class => 'dot-mail.service.default',
            ],
            'abstract_factories' => [
                MailServiceAbstractFactory::class,
                MailOptionsAbstractFactory::class,
            ],
        ];
    }
}
