<?php

declare(strict_types=1);

namespace Thingston\Tests\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Thingston\Cache\Adapter\MemoryAdapter;

final class MemoryAdapterTest extends TestCase
{
    use AdapterTestTrait;

    protected function createAdapter(): CacheItemPoolInterface
    {
        return new MemoryAdapter();
    }
}
