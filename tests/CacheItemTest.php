<?php

declare(strict_types=1);

namespace Thingston\Tests\Cache;

use DateInterval;
use DateTime;
use PHPUnit\Framework\TestCase;
use Thingston\Cache\CacheItem;
use Thingston\Cache\Exception\InvalidArgumentException;

final class CacheItemTest extends TestCase
{
    public function testCacheItem(): void
    {
        $item = new CacheItem('foo', 'bar', 3600);

        $this->assertTrue($item->isHit());
        $this->assertSame('foo', $item->getKey());
        $this->assertSame('bar', $item->get());
    }

    public function testExpiresAt(): void
    {
        $item = new CacheItem('foo', 'bar', -1);

        $this->assertFalse($item->isHit());
        $this->assertNull($item->get());

        $item->set('bar')->expiresAt(new DateTime('tomorrow'));

        $this->assertTrue($item->isHit());
        $this->assertSame('foo', $item->getKey());
        $this->assertSame('bar', $item->get());
    }

    public function testExpiresAfter(): void
    {
        $item = new CacheItem('foo', 'bar');
        $this->assertFalse($item->isHit());

        $item->expiresAfter(new DateInterval('PT1H'));
        $this->assertTrue($item->isHit());

        $item->expiresAfter(null);
        $this->assertFalse($item->isHit());
    }

    public function testInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CacheItem('', 'bar');
    }
}
