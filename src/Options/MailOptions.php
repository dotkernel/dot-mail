<?php

declare(strict_types=1);

namespace Dot\Mail\Options;

use Laminas\Stdlib\AbstractOptions;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

use function array_key_exists;
use function class_exists;
use function is_string;
use function strtolower;

/**
 * @extends AbstractOptions<mixed>
 */
class MailOptions extends AbstractOptions
{
    /** @var list<string|array{type?: mixed, priority?: mixed}> */
    protected array $eventListeners                = [];
    protected TransportInterface|string $transport = SendmailTransport::class;
    /** @var array<string, list<class-string<TransportInterface>>> */
    protected array $transportMap = [
        'esmtp'    => [EsmtpTransport::class],
        'sendmail' => [SendmailTransport::class],
    ];
    protected MessageOptions $messageOptions;
    protected SmtpOptions $smtpOptions;

    /**
     * @return array<string, list<class-string<TransportInterface>>>
     */
    public function getTransportMap(): array
    {
        return $this->transportMap;
    }

    /**
     * @param array<string, list<class-string<TransportInterface>>> $transportMap
     */
    public function setTransportMap(array $transportMap): void
    {
        $this->transportMap = $transportMap;
    }

    public function getTransport(): string|TransportInterface
    {
        return $this->transport;
    }

    public function setTransport(string|TransportInterface $transport): void
    {
        $key = is_string($transport) ? strtolower($transport) : null;
        if ($key !== null && array_key_exists($key, $this->transportMap)) {
            foreach ($this->transportMap[$key] as $class) {
                if (class_exists($class)) {
                    $transport = $class;
                    break;
                }
            }
        }

        $this->transport = $transport;
    }

    public function getMessageOptions(): MessageOptions
    {
        if (! isset($this->messageOptions)) {
            $this->messageOptions = new MessageOptions();
        }
        return $this->messageOptions;
    }

    /**
     * @param array<string, mixed> $messageOptions
     */
    public function setMessageOptions(array $messageOptions): void
    {
        $this->messageOptions = new MessageOptions($messageOptions);
    }

    public function getSmtpOptions(): SmtpOptions
    {
        if (! isset($this->smtpOptions)) {
            $this->smtpOptions = new SmtpOptions();
        }
        return $this->smtpOptions;
    }

    /**
     * @param array<string, mixed> $smtpOptions
     */
    public function setSmtpOptions(array $smtpOptions): void
    {
        $this->smtpOptions = new SmtpOptions($smtpOptions);
    }

    /**
     * @return list<string|array{type?: mixed, priority?: mixed}>
     */
    public function getEventListeners(): array
    {
        return $this->eventListeners;
    }

    /**
     * @param list<string|array{type?: mixed, priority?: mixed}> $eventListeners
     */
    public function setEventListeners(array $eventListeners): void
    {
        $this->eventListeners = $eventListeners;
    }
}
