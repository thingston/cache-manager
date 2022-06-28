<?php

declare(strict_types=1);

namespace Thingston\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Thingston\Cache\Exception\InvalidArgumentException;
use Thingston\Settings\SettingsInterface;

class CacheManager implements CacheManagerInterface
{
    /**
     * @var array<string, CacheItemPoolInterface>
     */
    private array $pools = [];

    private SettingsInterface $settings;

    public function __construct(?SettingsInterface $settings = null)
    {
        $this->settings = $settings ?? new CacheSettings();
    }

    public function getItemPool(?string $name = null): CacheItemPoolInterface
    {
        if (null === $name) {
            $name = CacheSettings::DEFAULT;
        }

        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        if (false === $this->settings->has($name)) {
            throw InvalidArgumentException::forInvalidName($name);
        }

        $config = $this->settings->get($name);

        if (is_string($config)) {
            return $this->getItemPool($config);
        }

        if (false === is_array($config) && false === $config instanceof SettingsInterface) {
            throw InvalidArgumentException::forInvalidConfig($name);
        }

        if ($config instanceof SettingsInterface) {
            $config = $config->toArray();
        }

        $adapter = $config[CacheSettings::ADAPTER] ?? null;
        $arguments = $config[CacheSettings::ARGUMENTS] ?? [];

        if (false === is_string($adapter) || false === is_a($adapter, CacheItemPoolInterface::class, true)) {
            throw InvalidArgumentException::forInvalidAdapter($name);
        }

        if (false === is_array($arguments)) {
            throw InvalidArgumentException::forInvalidArguments($name);
        }

        return $this->pools[$name] = new $adapter(...$arguments);
    }

    public function clear(): bool
    {
        return $this->getItemPool()->clear();
    }

    public function commit(): bool
    {
        return $this->getItemPool()->commit();
    }

    public function deleteItem(string $key): bool
    {
        return $this->getItemPool()->deleteItem($key);
    }

    public function deleteItems(array $keys): bool
    {
        return $this->getItemPool()->deleteItems($keys);
    }

    public function getItem(string $key): CacheItemInterface
    {
        return $this->getItemPool()->getItem($key);
    }

    /**
     * @param array<string> $keys
     * @return iterable<CacheItemInterface>
     */
    public function getItems(array $keys = []): iterable
    {
        return $this->getItemPool()->getItems($keys);
    }

    public function hasItem(string $key): bool
    {
        return $this->getItemPool()->hasItem($key);
    }

    public function save(CacheItemInterface $item): bool
    {
        return $this->getItemPool()->save($item);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->getItemPool()->saveDeferred($item);
    }
}
