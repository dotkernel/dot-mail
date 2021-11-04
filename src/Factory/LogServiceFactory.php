<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Factory;

use Dot\Mail\Service\LogService;
use Dot\Mail\Service\LogServiceInterface;
use Psr\Container\ContainerInterface;

/**
 * Class LogServiceFactory
 * @package Dot\Mail\Factory
 */
class LogServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return LogServiceInterface
     */
    public function __invoke(ContainerInterface $container): LogServiceInterface
    {
        $config = $container->get('config')['dot_mail'] ?? [];
        return new LogService($config);
    }
}
