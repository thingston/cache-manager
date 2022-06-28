<?php

declare(strict_types=1);

namespace Thingston\Cache\Exception;

use Psr\Cache\InvalidArgumentException;
use Throwable;

interface CacheExceptionInterface extends InvalidArgumentException, Throwable
{
}
