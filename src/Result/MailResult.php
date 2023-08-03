<?php

declare(strict_types=1);

namespace Dot\Mail\Result;

use Exception;

class MailResult implements ResultInterface
{
    public const DEFAULT_MESSAGE = 'Success!';

    protected bool $valid;
    protected string $message;
    protected Exception|null $exception;

    public function __construct(
        bool $valid = true,
        string $message = self::DEFAULT_MESSAGE,
        ?Exception $exception = null
    ) {
        $this->valid     = $valid;
        $this->message   = $message;
        $this->exception = $exception;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function hasException(): bool
    {
        return $this->exception instanceof Exception;
    }

    public function getException(): ?Exception
    {
        return $this->exception;
    }
}
