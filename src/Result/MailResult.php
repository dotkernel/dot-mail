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
 * Class MailResult
 * @package Dot\Mail\Result
 */
class MailResult implements ResultInterface
{
    const DEFAULT_MESSAGE = 'Success!';

    /** @var bool */
    protected $valid;

    /** @var string */
    protected $message;

    /** @var \Exception */
    protected $exception;

    /**
     * MailResult constructor.
     * @param bool $valid
     * @param string $message
     * @param \Exception|null $exception
     */
    public function __construct(
        bool $valid = true,
        string $message = self::DEFAULT_MESSAGE,
        \Exception $exception = null
    ) {
        $this->valid = $valid;
        $this->message = $message;
        $this->exception = $exception;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return bool
     */
    public function hasException(): bool
    {
        return $this->exception instanceof \Exception;
    }

    /**
     * @return \Exception|null
     */
    public function getException(): \Exception
    {
        return $this->exception;
    }
}
