<?php

declare(strict_types=1);

namespace Dot\Mail\Factory;

use Dot\Mail\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_key_exists;
use function count;
use function explode;
use function is_array;
use function sprintf;

abstract class AbstractMailFactory implements AbstractFactoryInterface
{
    public const DOT_MAIL_PART = 'dot-mail';
    public const SPECIFIC_PART = '';
    public const SERVICES_KEY  = 'services';
    public const LOG_KEY       = 'log';

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

        if (count($parts) !== 3 || $parts[0] !== self::DOT_MAIL_PART || $parts[1] !== static::SPECIFIC_PART) {
            return false;
        }

        $specificServiceName = $parts[2];
        $config              = $this->getConfig($container);
        return array_key_exists($specificServiceName, $config);
    }

    /**
     * Returns the configured mail services, indexed by name (the `dot_mail.services` key).
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException When the configuration uses the pre-6.0 layout.
     */
    public function getConfig(ContainerInterface $container): array
    {
        $config = $container->get('config');
        if (! isset($config[$this->configKey]) || ! is_array($config[$this->configKey])) {
            return $this->config;
        }

        $mailConfig = $config[$this->configKey];
        if (isset($mailConfig[self::SERVICES_KEY]) && is_array($mailConfig[self::SERVICES_KEY])) {
            $this->config = $mailConfig[self::SERVICES_KEY];
            return $this->config;
        }

        foreach ($mailConfig as $key => $value) {
            if ($key !== self::LOG_KEY && is_array($value)) {
                throw new InvalidArgumentException(sprintf(
                    'Mail service "%s" must be configured under "%s.%s"; see the upgrade guide to version 6',
                    $key,
                    $this->configKey,
                    self::SERVICES_KEY
                ));
            }
        }

        return $this->config;
    }
}
