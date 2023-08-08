<?php

declare(strict_types=1);

namespace Dot\Mail\Factory;

use Dot\Mail\Options\MailOptions;
use Laminas\Stdlib\ArrayUtils;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_key_exists;
use function explode;
use function is_array;
use function is_string;
use function trim;

class MailOptionsAbstractFactory extends AbstractMailFactory
{
    public const SPECIFIC_PART = 'options';

    /**
     * @param string $requestedName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MailOptions
    {
        $specificServiceName = explode('.', $requestedName)[2];

        $config         = $this->getConfig($container);
        $specificConfig = $config[$specificServiceName];
        if (! is_array($specificConfig)) {
            $specificConfig = [];
        }

        /**
         * Merge any extended mail service config into this one
         */
        do {
            $extendsConfigKey = isset($specificConfig['extends']) && is_string($specificConfig['extends'])
                ? trim($specificConfig['extends'])
                : null;

            unset($specificConfig['extends']);

            if (
                $extendsConfigKey !== null
                && array_key_exists($extendsConfigKey, $config)
                && is_array($config[$extendsConfigKey])
            ) {
                $specificConfig = ArrayUtils::merge($config[$extendsConfigKey], $specificConfig);
            }
        } while ($extendsConfigKey !== null);

        return new MailOptions($specificConfig);
    }
}
