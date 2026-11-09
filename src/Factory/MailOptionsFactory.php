<?php

declare(strict_types=1);

namespace Dot\Mail\Factory;

use Dot\Mail\Exception\InvalidArgumentException;
use Dot\Mail\Options\MailOptions;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Creates the options of a mail service, registered as `dot-mail.options.<name>`.
 * Works with any PSR-11 container that accepts a factory per service key.
 */
class MailOptionsFactory
{
    public const SPECIFIC_PART = 'options';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException When the service is not configured under dot_mail.services.
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName = MailConfig::DOT_MAIL_PART . '.' . self::SPECIFIC_PART . '.default'
    ): MailOptions {
        $name = MailConfig::serviceName($requestedName, self::SPECIFIC_PART);

        return new MailOptions(MailConfig::service($container, $name));
    }
}
