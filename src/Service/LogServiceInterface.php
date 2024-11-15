<?php

declare(strict_types=1);

namespace Dot\Mail\Service;

use Dot\Mail\Email;

interface LogServiceInterface
{
    public function sent(Email $message): false|int|null;
}
