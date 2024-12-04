<?php

declare(strict_types=1);

namespace Dot\Mail\Options;

use Laminas\Stdlib\AbstractOptions;
use Symfony\Component\Mailer\Exception\InvalidArgumentException;

use function sprintf;

/**
 * @extends AbstractOptions<string>
 */
class SmtpOptions extends AbstractOptions
{
    protected string $name            = 'localhost';
    protected array $connectionConfig = [];
    protected string $host            = '127.0.0.1';
    protected int $port               = 25;

    protected ?int $connectionTimeLimit;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the local client hostname or IP
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get connection configuration array
     */
    public function getConnectionConfig(): array
    {
        return $this->connectionConfig;
    }

    /**
     * Set connection configuration array
     */
    public function setConnectionConfig(array $connectionConfig): static
    {
        $this->connectionConfig = $connectionConfig;
        return $this;
    }

    /**
     * Get the host name
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set the SMTP host
     */
    public function setHost(string $host): static
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Get the port the SMTP server runs on
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Set the port the SMTP server runs on
     */
    public function setPort(int $port): static
    {
        if ($port < 1) {
            throw new InvalidArgumentException(sprintf(
                'Port must be greater than 1; received "%d"',
                $port
            ));
        }
        $this->port = $port;
        return $this;
    }

    public function getConnectionTimeLimit(): ?int
    {
        return $this->connectionTimeLimit;
    }

    public function setConnectionTimeLimit(?int $seconds): static
    {
        $this->connectionTimeLimit = $seconds ?? null;

        return $this;
    }
}
