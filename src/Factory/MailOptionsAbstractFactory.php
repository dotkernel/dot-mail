<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Factory;

use Dot\Mail\Options\MailOptions;
use Interop\Container\ContainerInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Class MailOptionsAbstractFactory
 * @package Dot\Mail\Factory
 */
class MailOptionsAbstractFactory extends AbstractMailFactory
{
    const SPECIFIC_PART = 'options';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return MailOptions
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MailOptions
    {
        $specificServiceName = explode('.', $requestedName)[2];

        $config = $this->getConfig($container);
        $specificConfig = $config[$specificServiceName];
        if (!is_array($specificConfig)) {
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

            if (!is_null($extendsConfigKey)
                && array_key_exists($extendsConfigKey, $config)
                && is_array($config[$extendsConfigKey])
            ) {
                $specificConfig = ArrayUtils::merge($config[$extendsConfigKey], $specificConfig);
            }
        } while ($extendsConfigKey != null);

        return new MailOptions($specificConfig);
    }
}
