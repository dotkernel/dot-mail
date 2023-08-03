<?php

declare(strict_types=1);

namespace Dot\Mail\Factory;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_key_exists;
use function count;
use function explode;
use function is_array;

abstract class AbstractMailFactory implements AbstractFactoryInterface
{
    public const DOT_MAIL_PART = 'dot-mail';
    public const SPECIFIC_PART = '';

    protected string $configKey = 'dot_mail';
    protected array $config     = [];

    /**
     * @param string $requestedName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        $parts = explode('.', $requestedName);
        if (count($parts) !== 3) {
            return false;
        }

        if ($parts[0] !== self::DOT_MAIL_PART || $parts[1] !== static::SPECIFIC_PART) {
            return false;
        }

        $specificServiceName = $parts[2];
        $config              = $this->getConfig($container);
        return array_key_exists($specificServiceName, $config);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getConfig(ContainerInterface $container): array
    {
        $config = $container->get('config');
        if (isset($config[$this->configKey]) && is_array($config[$this->configKey])) {
            $this->config = $config[$this->configKey];
        }

        return $this->config;
    }
}
