<?php

declare(strict_types=1);

namespace Thingston\Cache\Adapter;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

interface CacheAdapterInterface extends CacheItemPoolInterface, CacheInterface
{
}
