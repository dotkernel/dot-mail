<?php

declare(strict_types=1);

namespace Dot\Mail\Factory;

use Dot\Mail\Exception\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function count;
use function explode;
use function is_array;
use function sprintf;

/**
 * Reads the dot-mail configuration for the factories. Holds no state.
 */
final class MailConfig
{
    public const CONFIG_KEY    = 'dot_mail';
    public const DOT_MAIL_PART = 'dot-mail';
    public const SERVICES_KEY  = 'services';
    public const LOG_KEY       = 'log';

    /**
     * Returns the mail service name from a container key such as `dot-mail.service.default`.
     *
     * @throws InvalidArgumentException When the key does not have the form `dot-mail.<part>.<name>`.
     */
    public static function serviceName(string $requestedName, string $part): string
    {
        $parts = explode('.', $requestedName);
        if (count($parts) !== 3 || $parts[0] !== self::DOT_MAIL_PART || $parts[1] !== $part || $parts[2] === '') {
            throw new InvalidArgumentException(sprintf(
                'Mail service key must have the form "%s.%s.<name>"; "%s" given',
                self::DOT_MAIL_PART,
                $part,
                $requestedName
            ));
        }

        return $parts[2];
    }

    /**
     * Returns the configured mail services, indexed by name (the `dot_mail.services` key).
     *
     * @return array<string, mixed>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException When the configuration uses the pre-6.0 layout.
     */
    public static function services(ContainerInterface $container): array
    {
        $config = $container->has('config') ? $container->get('config') : [];
        if (! is_array($config) || ! isset($config[self::CONFIG_KEY]) || ! is_array($config[self::CONFIG_KEY])) {
            return [];
        }

        $mailConfig = $config[self::CONFIG_KEY];
        if (isset($mailConfig[self::SERVICES_KEY]) && is_array($mailConfig[self::SERVICES_KEY])) {
            return $mailConfig[self::SERVICES_KEY];
        }

        foreach ($mailConfig as $key => $value) {
            if ($key !== self::LOG_KEY && is_array($value)) {
                throw new InvalidArgumentException(sprintf(
                    'Mail service "%s" must be configured under "%s.%s"; see the upgrade guide to version 6',
                    $key,
                    self::CONFIG_KEY,
                    self::SERVICES_KEY
                ));
            }
        }

        return [];
    }

    /**
     * Returns the configuration of one mail service.
     *
     * @return array<string, mixed>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException When the service is not configured under `dot_mail.services`.
     */
    public static function service(ContainerInterface $container, string $name): array
    {
        $services = self::services($container);
        if (! isset($services[$name]) || ! is_array($services[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Mail service "%s" is not configured under "%s.%s"',
                $name,
                self::CONFIG_KEY,
                self::SERVICES_KEY
            ));
        }

        return $services[$name];
    }
}
