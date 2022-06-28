<?php

declare(strict_types=1);

namespace Thingston\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Thingston\Cache\CacheManager;
use Symfony\Component\Cache\CacheItem;

final class CacheItemTest extends TestCase
{
    public function testCacheItem(): void
    {
        $item = new \Thingston\Cache\CacheItem('foo', 'bar', 3600);

        $this->assertTrue($item->isHit());
        $this->assertSame('foo', $item->getKey());
        $this->assertSame('bar', $item->get());
    }

    public function testExpiresAt(): void
    {
        $item = new \Thingston\Cache\CacheItem('foo', 'bar');

        $this->assertFalse($item->isHit());
        $this->assertNull($item->get());

        $item->set('bar')->expiresAt(new \DateTime('tomorrow'));

        $this->assertTrue($item->isHit());
        $this->assertSame('foo', $item->getKey());
        $this->assertSame('bar', $item->get());
    }
}
