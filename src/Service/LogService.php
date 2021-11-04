<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Service;

use Laminas\Mail\AddressList;
use Laminas\Mail\Message;

use function date;
use function dirname;
use function file_exists;
use function file_put_contents;
use function mkdir;
use function sprintf;
use function trim;

/**
 * Class LogService
 * @package Dot\Mail\Service
 */
class LogService implements LogServiceInterface
{
    protected array $config = [];

    /**
     * LogService constructor.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param Message $message
     * @return false|int|void
     */
    public function sent(Message $message)
    {
        /**
         * If empty: logging is disabled
         */
        $target = $this->config['log']['sent'] ?? null;
        if (empty($target)) {
            return;
        }

        /**
         * Make sure that the directories in the target file's path exist
         */
        $dirname = dirname($target);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }

        /**
         * Prepare the data to be logged
         */
        $data = [
            'subject' => $message->getSubject(),
            'to' => $this->extractAddresses($message->getTo()),
            'cc' => $this->extractAddresses($message->getCc()),
            'bcc' => $this->extractAddresses($message->getBcc())
        ];
        $data = sprintf('[%s]: %s' . PHP_EOL, date('Y-m-d H:i:s'), json_encode($data));

        /**
         * Write the log data and return the result
         */
        return file_put_contents($target, $data, FILE_APPEND);
    }

    /**
     * Extract names+addresses pairs from an AddressList
     * @param AddressList $addressList
     * @return array
     */
    private function extractAddresses(AddressList $addressList): array
    {
        $addresses = [];
        foreach ($addressList as $address) {
            $addresses[] = trim(sprintf('%s <%s>', $address->getName(), $address->getEmail()));
        }
        return $addresses;
    }
}
