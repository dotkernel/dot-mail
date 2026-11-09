<?php

declare(strict_types=1);

namespace DotTest\Mail\Fixture;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ContainerErrorException extends RuntimeException implements ContainerExceptionInterface
{
}
