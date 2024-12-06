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
 * @template TValue
 * @template-extends AbstractOptions<TValue>
 */
class MailOptions extends AbstractOptions
{
    protected array $eventListeners                = [];
    protected TransportInterface|string $transport = EsmtpTransport::class;
    protected array $transportMap                  = [
        'esmtp'    => [EsmtpTransport::class],
        'sendmail' => [SendmailTransport::class],
    ];
    protected MessageOptions $messageOptions;
    protected SmtpOptions $smtpOptions;

    public function getTransportMap(): array
    {
        return $this->transportMap;
    }

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
        if (is_string($transport) && array_key_exists(strtolower($transport), $this->transportMap)) {
            $transport = $this->transportMap[$transport];
            foreach ($transport as $class) {
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
        return $this->messageOptions;
    }

    public function setMessageOptions(array $messageOptions): void
    {
        $this->messageOptions = new MessageOptions($messageOptions);
    }

    public function getSmtpOptions(): SmtpOptions
    {
        return $this->smtpOptions;
    }

    public function setSmtpOptions(array $smtpOptions): void
    {
        $this->smtpOptions = new SmtpOptions($smtpOptions);
    }

    public function getEventListeners(): array
    {
        return $this->eventListeners;
    }

    public function setEventListeners(array $eventListeners): void
    {
        $this->eventListeners = $eventListeners;
    }
}
