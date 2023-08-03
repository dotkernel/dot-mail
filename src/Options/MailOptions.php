<?php

declare(strict_types=1);

namespace Dot\Mail\Options;

use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\FileOptions;
use Laminas\Mail\Transport\InMemory;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Stdlib\AbstractOptions;

use function array_key_exists;
use function class_exists;
use function is_string;
use function strtolower;

class MailOptions extends AbstractOptions
{
    protected array $eventListeners                = [];
    protected array $saveSentMessageFolder         = [];
    protected TransportInterface|string $transport = Sendmail::class;
    protected array $transportMap                  = [
        'sendmail'  => [Sendmail::class],
        'smtp'      => [Smtp::class],
        'in_memory' => [InMemory::class],
        'file'      => [File::class],
    ];
    protected MessageOptions $messageOptions;
    protected SmtpOptions $smtpOptions;
    protected FileOptions $fileOptions;

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

    public function getFileOptions(): FileOptions
    {
        return $this->fileOptions;
    }

    public function setFileOptions(array $fileOptions): void
    {
        $this->fileOptions = new FileOptions($fileOptions);
    }

    public function getEventListeners(): array
    {
        return $this->eventListeners;
    }

    public function setEventListeners(array $eventListeners): void
    {
        $this->eventListeners = $eventListeners;
    }

    public function getSaveSentMessageFolder(): array
    {
        return $this->saveSentMessageFolder;
    }

    public function setSaveSentMessageFolder(array $saveSentMessageFolder): void
    {
        $this->saveSentMessageFolder = $saveSentMessageFolder;
    }
}
