<?php

declare(strict_types=1);

namespace Thingston\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Thingston\Cache\Adapter\CacheAdapterInterface;
use Thingston\Cache\Adapter\MemoryAdapter;
use Thingston\Cache\CacheManager;
use Thingston\Cache\CacheSettings;
use Thingston\Cache\Exception\InvalidArgumentException;
use Thingston\Settings\Settings;

final class CacheManagerTest extends TestCase
{
    use Adapter\AdapterTestTrait;

    public function testDefaultPool(): void
    {
        $manager = new CacheManager();

        $this->assertInstanceOf(CacheAdapterInterface::class, $manager->getCache());
    }

    public function testNamedPools(): void
    {
        $manager = new CacheManager();

        $this->assertInstanceOf(CacheAdapterInterface::class, $manager->getCache('default'));
        $this->assertInstanceOf(CacheAdapterInterface::class, $manager->getCache('file'));
    }

    public function testInvalidPoolName(): void
    {
        $manager = new CacheManager();
        $this->expectException(InvalidArgumentException::class);
        $manager->getCache('foo');
    }

    public function testInvalidConfig(): void
    {
        $manager = new CacheManager(new Settings([
            CacheSettings::DEFAULT => true,
        ]));

        $this->expectException(InvalidArgumentException::class);
        $manager->getCache();
    }

    public function testInvalidAdapter(): void
    {
        $manager = new CacheManager(new Settings([
            CacheSettings::DEFAULT => [
                CacheSettings::ADAPTER => 'NotAdapter',
            ],
        ]));

        $this->expectException(InvalidArgumentException::class);
        $manager->getCache();
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
        $manager->getCache();
    }

    protected function createAdapter(): CacheAdapterInterface
    {
        return new CacheManager();
    }
}
