<?php

declare(strict_types=1);

namespace Dot\Mail\Service;

use Laminas\Mail\Message;

interface LogServiceInterface
{
    public function sent(Message $message): false|int|null;
}
