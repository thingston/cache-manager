<?php

declare(strict_types=1);

namespace Thingston\Tests\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use Thingston\Cache\Adapter\CacheAdapterInterface;
use Thingston\Cache\Adapter\DbalAdapter;
use Thingston\Cache\Exception\InvalidArgumentException;

final class DbalAdapterTest extends TestCase
{
    use AdapterTestTrait;

    protected function createAdapter(): CacheAdapterInterface
    {
        return new DbalAdapter([
            'url' => 'sqlite:///' . sys_get_temp_dir() . '/' . uniqid() . '.cache',
        ]);
    }

    public function testBadAdapter(): void
    {
        $adapter = new DbalAdapter([
            'url' => 'foo',
        ]);

        $this->assertFalse($adapter->set('foo', 'bar', 60));
        $this->assertFalse($adapter->delete('foo'));

        $this->expectException(InvalidArgumentException::class);
        $this->assertFalse($adapter->has('foo'));
    }
}
