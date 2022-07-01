<?php

declare(strict_types=1);

namespace Thingston\Cache;

use Thingston\Cache\Adapter\CacheAdapterInterface;

interface CacheManagerInterface extends CacheAdapterInterface
{
    public function getCache(?string $name = null): CacheAdapterInterface;
}
