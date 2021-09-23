<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Service;

use Laminas\Mail\Message;

/**
 * Class LogServiceInterface
 * @package Dot\Mail\Service
 */
interface LogServiceInterface
{
    /**
     * @param Message $message
     * @return false|int|void
     */
    public function sent(Message $message);
}
