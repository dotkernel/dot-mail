<?php

declare(strict_types=1);

namespace Dot\Mail\Result;

use Exception;

interface ResultInterface
{
    public function getMessage(): string;

    public function isValid(): bool;

    public function hasException(): bool;

    public function getException(): ?Exception;
}
