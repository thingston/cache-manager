<?php

declare(strict_types=1);

namespace Thingston\Tests\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use Thingston\Cache\Adapter\CacheAdapterInterface;
use Thingston\Cache\Adapter\DbalAdapter;

final class DbalAdapterTest extends TestCase
{
    use AdapterTestTrait;

    protected function createAdapter(): CacheAdapterInterface
    {
        return new DbalAdapter([
            'url' => 'sqlite:///:memory:',
        ]);
    }
}
