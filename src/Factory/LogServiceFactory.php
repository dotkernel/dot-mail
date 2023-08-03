<?php

declare(strict_types=1);

namespace Dot\Mail\Factory;

use Dot\Mail\Service\LogService;
use Dot\Mail\Service\LogServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class LogServiceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LogServiceInterface
    {
        $config = $container->get('config')['dot_mail'] ?? [];
        return new LogService($config);
    }
}
