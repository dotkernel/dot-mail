<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

declare(strict_types = 1);

namespace Dot\Mail\Result;

/**
 * Interface ResultInterface
 * @package Dot\Mail\Result
 */
interface ResultInterface
{
    /**
     * Get error message when error occurs
     * @return string
     */
    public function getMessage(): string;

    /**
     * Tells if the MailService that produced this result was properly sent
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Tells if Result has an Exception
     * @return bool
     */
    public function hasException(): bool;

    /**
     * @return \Exception
     */
    public function getException(): \Exception;
}
