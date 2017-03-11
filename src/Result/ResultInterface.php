<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
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
