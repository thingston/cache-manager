<?php

declare(strict_types=1);

namespace Thingston\Tests\Cache\Adapter;

use Thingston\Cache\Adapter\CacheAdapterInterface;
use Thingston\Cache\CacheItem;
use Thingston\Cache\Exception\InvalidArgumentException;

trait AdapterTestTrait
{
    abstract protected function createAdapter(): CacheAdapterInterface;
    abstract public function expectException(string $exception): void;
    abstract public static function assertEquals($expected, $actual, string $message = ''): void;
    abstract public static function assertTrue($conditionm, string $message = ''): void;
    abstract public static function assertFalse($conditionm, string $message = ''): void;
    abstract public static function assertCount(int $expectedCount, $haystack, string $message = ''): void;
    abstract public static function assertSame($expected, $actual, string $message = '');

    public function testInvalidKey(): void
    {
        $adapter = $this->createAdapter();
        $this->expectException(InvalidArgumentException::class);
        $adapter->hasItem('');
    }

    public function testHasItem(): void
    {
        $adapter = $this->createAdapter();

        $item = new CacheItem('foo', 'bar', 60);

        $this->assertFalse($adapter->hasItem($item->getKey()));
        $this->assertTrue($adapter->save($item));
        $this->assertTrue($adapter->hasItem($item->getKey()));
    }

    public function testHasItemExpired(): void
    {
        $adapter = $this->createAdapter();

        $item = new CacheItem('foo', 'bar', 1);

        $this->assertFalse($adapter->hasItem($item->getKey()));
        $this->assertTrue($adapter->save($item));
        $this->assertTrue($adapter->hasItem($item->getKey()));

        sleep(1);
        $this->assertFalse($adapter->hasItem($item->getKey()));
    }

    public function testSaveGetDeleteItem(): void
    {
        $adapter = $this->createAdapter();
        $item = new CacheItem('foo', 'bar', 60);

        $this->assertFalse($adapter->hasItem($item->getKey()));
        $this->assertFalse($adapter->deleteItem($item->getKey()));

        $this->assertTrue($adapter->save($item));
        $this->assertTrue($adapter->hasItem($item->getKey()));
        $this->assertEquals($item, $adapter->getItem($item->getKey()));

        $this->assertTrue($adapter->deleteItem($item->getKey()));
        $this->assertFalse($adapter->hasItem($item->getKey()));
    }

    public function testSaveDeferredItem(): void
    {
        $adapter = $this->createAdapter();
        $item = new CacheItem('foo', 'bar', 60);

        $this->assertTrue($adapter->saveDeferred($item));
        $this->assertFalse($adapter->hasItem($item->getKey()));
        $this->assertTrue($adapter->commit());
        $this->assertTrue($adapter->hasItem($item->getKey()));
    }

    public function testCommitExpired(): void
    {
        $adapter = $this->createAdapter();
        $item = new CacheItem('foo', 'bar');

        $this->assertTrue($adapter->saveDeferred($item));
        $this->assertFalse($adapter->hasItem($item->getKey()));
        $this->assertFalse($adapter->commit());
        $this->assertFalse($adapter->hasItem($item->getKey()));
    }

    public function testGetItems(): void
    {
        $adapter = $this->createAdapter();

        $adapter->save(new CacheItem('foo', 'bar', 60));
        $adapter->save(new CacheItem('bar', 'baz', 60));
        $adapter->save(new CacheItem('baz', 'bee', 60));

        $this->assertCount(3, $adapter->getItems(['foo', 'bar', 'baz']));
    }

    public function testDeleteItems(): void
    {
        $adapter = $this->createAdapter();

        $adapter->save(new CacheItem('foo', 'bar', 60));
        $adapter->save(new CacheItem('bar', 'baz', 60));
        $adapter->save(new CacheItem('baz', 'bee', 60));

        $this->assertTrue($adapter->deleteItems(['foo', 'bar', 'baz']));
    }

    public function testDeleteItemsExpired(): void
    {
        $adapter = $this->createAdapter();

        $adapter->save(new CacheItem('foo', 'bar', 60));
        $adapter->save(new CacheItem('bar', 'baz', 60));
        $adapter->save(new CacheItem('baz', 'bee'));

        $this->assertFalse($adapter->deleteItems(['foo', 'bar', 'baz']));
    }

    public function testClear(): void
    {
        $adapter = $this->createAdapter();

        $adapter->save(new CacheItem('foo', 'bar', 60));
        $adapter->save(new CacheItem('bar', 'baz', 60));
        $adapter->save(new CacheItem('baz', 'bee', 60));

        $this->assertTrue($adapter->hasItem('foo'));
        $this->assertTrue($adapter->hasItem('bar'));
        $this->assertTrue($adapter->hasItem('baz'));

        $this->assertTrue($adapter->clear());

        $this->assertFalse($adapter->hasItem('foo'));
        $this->assertFalse($adapter->hasItem('bar'));
        $this->assertFalse($adapter->hasItem('baz'));
    }

    public function testGet(): void
    {
        $adapter = $this->createAdapter();
        $adapter->save(new CacheItem('foo', 'bar', 60));

        $this->assertSame('bar', $adapter->get('foo', 'default'));
        $this->assertSame('default', $adapter->get('bar', 'default'));
    }

    public function testSetDelete(): void
    {
        $adapter = $this->createAdapter();

        $this->assertTrue($adapter->set('foo', 'bar', 60));
        $this->assertTrue($adapter->has('foo'));
        $this->assertTrue($adapter->delete('foo'));
        $this->assertFalse($adapter->set('foo', 'bar'));
        $this->assertFalse($adapter->delete('foo'));
        $this->assertFalse($adapter->has('foo'));
    }

    public function testSetGetDeleteMultiple(): void
    {
        $adapter = $this->createAdapter();

        $values = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'bee',
        ];

        $this->assertTrue($adapter->setMultiple($values, 60));
        $this->assertSame($values, $adapter->getMultiple(array_keys($values)));
        $this->assertTrue($adapter->deleteMultiple(array_keys($values)));

        $this->assertFalse($adapter->setMultiple(['foo' => 'bar']));
        $this->assertSame(['foo' => null], $adapter->getMultiple(['foo']));
        $this->assertFalse($adapter->deleteMultiple(['foo']));
    }
}
