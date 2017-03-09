<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

declare(strict_types = 1);

namespace Dot\Mail;

use Dot\Mail\Factory\MailOptionsAbstractFactory;
use Dot\Mail\Factory\MailServiceAbstractFactory;

/**
 * Class ConfigProvider
 * @package Dot\Mail
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [

            'dependencies' => $this->getDependencyConfig(),

            'dot_mail' => [],

        ];
    }

    /**
     * @return array
     */
    public function getDependencyConfig(): array
    {
        return [
            'abstract_factories' => [
                MailServiceAbstractFactory::class,
                MailOptionsAbstractFactory::class,
            ]
        ];
    }
}
