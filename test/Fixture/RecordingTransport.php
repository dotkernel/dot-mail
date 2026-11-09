<?php

declare(strict_types=1);

namespace DotTest\Mail\Fixture;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\RawMessage;

/**
 * Transport that runs Symfony's message validation (through SentMessage) and records what was sent.
 */
class RecordingTransport extends AbstractTransport
{
    /** @var list<RawMessage> */
    public array $sent = [];

    protected function doSend(SentMessage $message): void
    {
        $this->sent[] = $message->getOriginalMessage();
    }

    public function __toString(): string
    {
        return 'recording://';
    }
}
