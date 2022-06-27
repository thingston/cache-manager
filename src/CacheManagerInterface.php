<?php

declare(strict_types=1);

namespace Thingston\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

interface CacheManagerInterface extends CacheItemPoolInterface
{
    public function getItemPool(?string $name = null): CacheItemPoolInterface;
}
