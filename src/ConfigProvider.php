<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace DotKernel\DotMail;

use DotKernel\DotMail\Factory\MailOptionsAbstractFactory;
use DotKernel\DotMail\Factory\MailServiceAbstractFactory;

/**
 * Class ConfigProvider
 * @package DotKernel\DotMail
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke()
    {
        return [

            'dependencies' => $this->getDependencyConfig(),

            'dot_mail' => [

            ]

        ];
    }

    /**
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'abstract_factories' => [
                MailServiceAbstractFactory::class,
                MailOptionsAbstractFactory::class,
            ]
        ];
    }
}