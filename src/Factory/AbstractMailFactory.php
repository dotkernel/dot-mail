<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Class AbstractMailFactory
 * @package Dot\Mail\Factory
 */
abstract class AbstractMailFactory implements AbstractFactoryInterface
{
    const DOT_MAIL_PART = 'dot-mail';
    const SPECIFIC_PART = '';

    /** @var string */
    protected $configKey = 'dot_mail';

    /** @var  array */
    protected $config = [];

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $parts = explode('.', $requestedName);
        if (count($parts) !== 3) {
            return false;
        }

        if ($parts[0] !== self::DOT_MAIL_PART || $parts[1] !== static::SPECIFIC_PART) {
            return false;
        }

        $specificServiceName = $parts[2];
        $config = $this->getConfig($container);
        return array_key_exists($specificServiceName, $config);
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    protected function getConfig(ContainerInterface $container): array
    {
        $config = $container->get('config');
        if (isset($config[$this->configKey]) && is_array($config[$this->configKey])) {
            $this->config = $config[$this->configKey];
        }

        return $this->config;
    }
}
