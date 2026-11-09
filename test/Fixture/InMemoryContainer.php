<?php

declare(strict_types=1);

namespace DotTest\Mail\Fixture;

use Closure;
use Psr\Container\ContainerInterface;
use Throwable;

use function array_key_exists;
use function sprintf;

/**
 * Minimal PSR-11 container for the tests.
 *
 * A Closure value is a lazy factory: it receives the container and its result is shared.
 * A failure inside it is wrapped in a ContainerErrorException, as real containers do.
 */
class InMemoryContainer implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $services = [];

    /**
     * @param array<string, mixed> $services
     */
    public function __construct(array $services = [])
    {
        foreach ($services as $id => $service) {
            $this->set($id, $service);
        }
    }

    public function set(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
    }

    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new ServiceNotFoundException(sprintf('Service "%s" was not found', $id));
        }

        $service = $this->services[$id];
        if ($service instanceof Closure) {
            try {
                $service = $service($this);
            } catch (Throwable $e) {
                throw new ContainerErrorException(sprintf('Service "%s" could not be created', $id), 0, $e);
            }
            $this->services[$id] = $service;
        }

        return $service;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }
}
