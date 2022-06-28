<?php

declare(strict_types=1);

namespace Thingston\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Thingston\Cache\Adapter\MemoryAdapter;
use Thingston\Cache\CacheItem;
use Thingston\Cache\CacheManager;
use Thingston\Cache\CacheSettings;
use Thingston\Cache\Exception\InvalidArgumentException;
use Thingston\Settings\Settings;

final class CacheManagerTest extends TestCase
{
    public function testDefaultPool(): void
    {
        $manager = new CacheManager();

        $this->assertInstanceOf(CacheItemPoolInterface::class, $manager->getItemPool());
    }

    public function testNamedPools(): void
    {
        $manager = new CacheManager();

        $this->assertInstanceOf(CacheItemPoolInterface::class, $manager->getItemPool('default'));
    }

    public function testManagerIsAlsoPool(): void
    {
        $manager = new CacheManager();

        $this->assertInstanceOf(CacheItemPoolInterface::class, $manager);
        $this->assertFalse($manager->hasItem('foo'));

        $item = new CacheItem('foo', 'bar', 60);

        $this->assertTrue($manager->save($item));
        $this->assertTrue($manager->hasItem('foo'));
        $this->assertEquals($item, $manager->getItem('foo'));
        $this->assertTrue($manager->deleteItems(['foo']));
        $this->assertFalse($manager->hasItem('foo'));

        $this->assertTrue($manager->saveDeferred($item));
        $this->assertTrue($manager->commit());
        $this->assertTrue($manager->hasItem('foo'));
        $this->assertEquals($item, $manager->getItem('foo'));
        $this->assertTrue($manager->clear());
        $this->assertFalse($manager->hasItem('foo'));
    }

    public function testInvalidPoolName(): void
    {
        $manager = new CacheManager();
        $this->expectException(InvalidArgumentException::class);
        $manager->getItemPool('foo');
    }

    public function testInvalidConfig(): void
    {
        $manager = new CacheManager(new Settings([
            CacheSettings::DEFAULT => true,
        ]));

        $this->expectException(InvalidArgumentException::class);
        $manager->getItemPool();
    }

    public function testInvalidAdapter(): void
    {
        $manager = new CacheManager(new Settings([
            CacheSettings::DEFAULT => [
                CacheSettings::ADAPTER => 'NotAdapter',
            ],
        ]));

        $this->expectException(InvalidArgumentException::class);
        $manager->getItemPool();
    }

    public function testInvalidArguments(): void
    {
        $manager = new CacheManager(new Settings([
            CacheSettings::DEFAULT => [
                CacheSettings::ADAPTER => MemoryAdapter::class,
                CacheSettings::ARGUMENTS => true,
            ],
        ]));

        $this->expectException(InvalidArgumentException::class);
        $manager->getItemPool();
    }
}
