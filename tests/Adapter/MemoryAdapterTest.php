<?php

declare(strict_types=1);

namespace Thingston\Tests\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use Thingston\Cache\Adapter\MemoryAdapter;
use Thingston\Cache\CacheItem;
use Thingston\Cache\Exception\InvalidArgumentException;

final class MemoryAdapterTest extends TestCase
{
    public function testMemoryAdapter(): void
    {
        $adapter = new MemoryAdapter();
        $item = new CacheItem('foo', 'bar', 60);

        $this->assertFalse($adapter->hasItem('foo'));
        $this->assertTrue($adapter->save($item));
        $this->assertTrue($adapter->hasItem('foo'));
        $this->assertEquals([$item], $adapter->getItems(['foo']));
        $this->assertTrue($adapter->deleteItems(['foo']));
        $this->assertFalse($adapter->hasItem('foo'));
        $this->assertTrue($adapter->saveDeferred($item));
        $this->assertTrue($adapter->commit());
        $this->assertTrue($adapter->hasItem('foo'));
        $this->assertTrue($adapter->clear());
        $this->assertFalse($adapter->hasItem('foo'));
        $this->assertFalse($adapter->save(new CacheItem('foo', 'bar', -1)));
        $this->assertTrue($adapter->save(new CacheItem('foo', 'bar', 1)));
        sleep(2);
        $this->assertFalse($adapter->hasItem('foo'));
    }

    public function testGetItemWithInvalidKey(): void
    {
        $adapter = new MemoryAdapter();
        $this->expectException(InvalidArgumentException::class);
        $adapter->getItem('');
    }

    public function testDeleteItemWithInvalidKey(): void
    {
        $adapter = new MemoryAdapter();
        $this->expectException(InvalidArgumentException::class);
        $adapter->deleteItem('');
    }

    public function testHasItemWithInvalidKey(): void
    {
        $adapter = new MemoryAdapter();
        $this->expectException(InvalidArgumentException::class);
        $adapter->hasItem('');
    }
}
