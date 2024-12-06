<?php

declare(strict_types=1);

namespace Dot\Mail\Factory;

use Dot\Mail\Options\MailOptions;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function explode;
use function is_array;

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

        return new MailOptions($specificConfig);
    }
}
