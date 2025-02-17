<?php

declare(strict_types=1);

namespace Dot\Mail\Service;

use DateTimeImmutable;
use Dot\Mail\Email;

use function date;
use function dirname;
use function file_exists;
use function file_put_contents;
use function json_encode;
use function mkdir;
use function sprintf;
use function trim;

use const FILE_APPEND;
use const PHP_EOL;

class LogService implements LogServiceInterface
{
    protected array $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function sent(Email $message): false|int|null
    {
        /**
         * If empty: logging is disabled
         */
        $target = $this->config['log']['sent'] ?? null;

        if (empty($target)) {
            return null;
        }

        /**
         * Make sure that the directories in the target file's path exist
         */
        $dirname = dirname($target);

        if (! file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }

        /**
         * Prepare the data to be logged
         */
        $data = [
            'subject' => $message->getSubject(),
            'to'      => $this->extractAddresses($message->getTo()),
            'cc'      => $this->extractAddresses($message->getCc()),
            'bcc'     => $this->extractAddresses($message->getBcc()),
        ];

        $date = new DateTimeImmutable();
        $data = sprintf('[%s]: %s' . PHP_EOL, $date->format('Y-m-d H:i:s'), json_encode($data));

        /**
         * Write the log data and return the result
         */
        return file_put_contents($target, $data, FILE_APPEND);
    }

    public function extractAddresses(array $addressList): array
    {
        $addresses = [];
        foreach ($addressList as $address) {
            $addresses[] = sprintf('%s <%s>', trim($address->getName() ?? ''), trim($address->getAddress()));
        }
        return $addresses;
    }
}
