<?php

declare(strict_types=1);

namespace Thingston\Cache;

use Thingston\Cache\Adapter\DbalAdapter;
use Thingston\Cache\Adapter\FileAdapter;
use Thingston\Cache\Adapter\MemoryAdapter;
use Thingston\Settings\AbstractSettings;

final class CacheSettings extends AbstractSettings
{
    public const DEFAULT = 'default';
    public const ADAPTER = 'adapter';
    public const ARGUMENTS = 'arguments';

    public function __construct()
    {
        parent::__construct([
            self::DEFAULT => 'memory',
            'memory' => [
                self::ADAPTER => MemoryAdapter::class,
            ],
            'file' => [
                self::ADAPTER => FileAdapter::class,
            ],
            'dbal' => [
                self::ADAPTER => DbalAdapter::class,
                self::ARGUMENTS => [
                    'params' => ['url' => 'sqlite:///:memory:',],
                ],
            ],
        ]);
    }
}
