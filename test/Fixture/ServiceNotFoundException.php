<?php

declare(strict_types=1);

namespace DotTest\Mail\Fixture;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class ServiceNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
