<?php

declare(strict_types=1);

namespace Thingston\Tests\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Thingston\Cache\Adapter\FileAdapter;
use Thingston\Cache\CacheItem;
use Thingston\Cache\Exception\InvalidArgumentException;

final class FileAdapterTest extends TestCase
{
    use AdapterTestTrait;

    protected function createAdapter(): CacheItemPoolInterface
    {
        return new FileAdapter(sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid());
    }

    public function testDefaultDirectory(): void
    {
        $adapter = new FileAdapter();

        $this->assertTrue($adapter->clear());
    }

    public function testRelativeDirectory(): void
    {
        $directory = 'cache' . DIRECTORY_SEPARATOR . uniqid();

        $path = getcwd() . DIRECTORY_SEPARATOR . $directory;
        $this->assertFalse(is_dir($path));

        $adapter = new FileAdapter($directory);

        $this->assertTrue(is_dir($path));
        $this->assertTrue($adapter->clear());
    }

    public function testNonWritableDirectory(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $this->assertTrue(mkdir($directory, 0400, true));

        $this->expectException(InvalidArgumentException::class);
        new FileAdapter($directory);
    }

    public function testFailCreatingDirectory(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $this->assertNotFalse(file_put_contents($directory, ''));

        $this->expectException(InvalidArgumentException::class);
        new FileAdapter($directory);
    }

    public function testFailClearingDirectory(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $adapter = new FileAdapter($directory);
        $this->assertTrue(rmdir($directory));

        $this->expectException(InvalidArgumentException::class);
        $adapter->clear();
    }

    public function testClearWithSubDirectory(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $subdir = $directory . DIRECTORY_SEPARATOR . uniqid();

        $adapter = new FileAdapter($directory);

        $this->assertTrue(mkdir($subdir, 0777, true));
        $this->assertTrue($adapter->clear());
        $this->assertFalse(is_dir($subdir));
    }

    public function testRemovedFile(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $adapter = new FileAdapter($directory);

        $item = new CacheItem('foo', 'bar', 60);
        $this->assertTrue($adapter->save($item));

        $file = $directory . DIRECTORY_SEPARATOR . hash('crc32', $item->getKey());
        $this->assertTrue(unlink($file));

        $this->assertNotEquals($item, $adapter->getItem('foo'));
    }

    public function testInvalidFile(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $adapter = new FileAdapter($directory);

        $item = new CacheItem('foo', 'bar', 60);
        $this->assertTrue($adapter->save($item));

        $file = $directory . DIRECTORY_SEPARATOR . hash('crc32', $item->getKey());
        $this->assertNotFalse(file_put_contents($file, 'foo'));

        $this->expectException(InvalidArgumentException::class);
        $adapter->getItem('foo');
    }

    public function testInvalidContent(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $adapter = new FileAdapter($directory);

        $item = new CacheItem('foo', 'bar', 60);
        $this->assertTrue($adapter->save($item));

        $file = $directory . DIRECTORY_SEPARATOR . hash('crc32', $item->getKey());
        $this->assertNotFalse(file_put_contents($file, serialize('foo')));

        $this->assertNotEquals($item, $adapter->getItem('foo'));
    }

    public function testFailRemoveItem(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $adapter = new FileAdapter($directory);

        $item = new CacheItem('foo', 'bar', 60);
        $this->assertTrue($adapter->save($item));

        $file = $directory . DIRECTORY_SEPARATOR . hash('crc32', $item->getKey());
        $this->assertTrue(unlink($file));

        $this->assertFalse($adapter->deleteItem('foo'));
    }

    public function testFailWritingItem(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $adapter = new FileAdapter($directory);

        $item = new CacheItem('foo', 'bar', 60);
        $this->assertTrue($adapter->save($item));

        $file = $directory . DIRECTORY_SEPARATOR . hash('crc32', $item->getKey());
        $this->assertTrue(unlink($file));
        $this->assertTrue(mkdir($file));

        $this->assertFalse($adapter->save($item));
    }
}
