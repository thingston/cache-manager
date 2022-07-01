<?php

declare(strict_types=1);

namespace Thingston\Cache;

use DateInterval;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Thingston\Cache\Adapter\CacheAdapterInterface;
use Thingston\Cache\Exception\InvalidArgumentException;
use Thingston\Settings\SettingsInterface;

class CacheManager implements CacheManagerInterface
{
    /**
     * @var array<string, CacheAdapterInterface>
     */
    private array $pools = [];

    private SettingsInterface $settings;

    public function __construct(?SettingsInterface $settings = null)
    {
        $this->settings = $settings ?? new CacheSettings();
    }

    public function getCache(?string $name = null): CacheAdapterInterface
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
            return $this->getCache($config);
        }

        if (false === is_array($config) && false === $config instanceof SettingsInterface) {
            throw InvalidArgumentException::forInvalidConfig($name);
        }

        if ($config instanceof SettingsInterface) {
            $config = $config->toArray();
        }

        $adapter = $config[CacheSettings::ADAPTER] ?? null;
        $arguments = $config[CacheSettings::ARGUMENTS] ?? [];

        if (false === is_string($adapter) || false === is_a($adapter, CacheAdapterInterface::class, true)) {
            throw InvalidArgumentException::forInvalidAdapter($name);
        }

        if (false === is_array($arguments)) {
            throw InvalidArgumentException::forInvalidArguments($name);
        }

        return $this->pools[$name] = new $adapter(...$arguments);
    }

    public function clear(): bool
    {
        return $this->getCache()->clear();
    }

    public function commit(): bool
    {
        return $this->getCache()->commit();
    }

    public function deleteItem(string $key): bool
    {
        return $this->getCache()->deleteItem($key);
    }

    public function deleteItems(array $keys): bool
    {
        return $this->getCache()->deleteItems($keys);
    }

    public function getItem(string $key): CacheItemInterface
    {
        return $this->getCache()->getItem($key);
    }

    /**
     * @param array<string> $keys
     * @return iterable<CacheItemInterface>
     */
    public function getItems(array $keys = []): iterable
    {
        return $this->getCache()->getItems($keys);
    }

    public function hasItem(string $key): bool
    {
        return $this->getCache()->hasItem($key);
    }

    public function save(CacheItemInterface $item): bool
    {
        return $this->getCache()->save($item);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->getCache()->saveDeferred($item);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getCache()->get($key, $default);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return $this->getCache()->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->getCache()->delete($key);
    }

    /**
     * @param iterable<string> $keys
     * @param mixed $default
     * @return iterable<mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->getCache()->getMultiple($keys, $default);
    }

    /**
     * @param iterable<string, mixed> $values
     * @param null|int|DateInterval $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        return $this->getCache()->setMultiple($values, $ttl);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->getCache()->deleteMultiple($keys);
    }

    public function has(string $key): bool
    {
        return $this->getCache()->has($key);
    }
}
