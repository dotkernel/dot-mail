<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace DotKernel\DotMail\Result;

/**
 * Interface ResultInterface
 * @package DotKernel\DotMail\Result
 */
interface ResultInterface
{
    /**
     * Get error message when error occurs
     * @return string
     */
    public function getMessage();

    /**
     * Tells if the MailService that produced this result was properly sent
     * @return bool
     */
    public function isValid();

    /**
     * Tells if Result has an Exception
     * @return bool
     */
    public function hasException();

    /**
     * @return \Exception
     */
    public function getException();
}