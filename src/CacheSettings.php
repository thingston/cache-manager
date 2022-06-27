<?php

declare(strict_types=1);

namespace Thingston\Cache;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Thingston\Settings\AbstractSettings;

final class CacheSettings extends AbstractSettings
{
    public const DEFAULT = 'default';
    public const ADAPTER = 'adapter';
    public const ARGUMENTS = 'arguments';

    public function __construct()
    {
        parent::__construct([
            self::DEFAULT => 'array',
            'array' => [
                self::ADAPTER => ArrayAdapter::class,
                self::ARGUMENTS => [
                    'defaultLifetime' => 0,
                ],
            ],
        ]);
    }
}
