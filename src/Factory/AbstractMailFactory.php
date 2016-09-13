<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace DotKernel\DotMail\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Class AbstractMailFactory
 * @package DotKernel\DotMail\Factory
 */
abstract class AbstractMailFactory implements AbstractFactoryInterface
{
    const DOT_MAIL_PART = 'dot-mail';
    const SPECIFIC_PART = '';

    /** @var string  */
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
        if(count($parts) !== 3) {
            return false;
        }

        if($parts[0] !== self::DOT_MAIL_PART || $parts[1] !== static::SPECIFIC_PART) {
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
    protected function getConfig(ContainerInterface $container)
    {
        $config = $container->get('config');
        if(isset($config[$this->configKey]) && is_array($this->configKey)) {
            $this->config = $config[$this->configKey];
        }

        return $this->config;
    }
}