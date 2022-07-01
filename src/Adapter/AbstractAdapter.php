<?php

declare(strict_types=1);

namespace Thingston\Cache\Adapter;

use DateInterval;
use Psr\Cache\CacheItemInterface;
use Thingston\Cache\CacheItem;
use Thingston\Cache\Exception\InvalidArgumentException;

abstract class AbstractAdapter implements CacheAdapterInterface
{
    /**
     * @var array<CacheItemInterface>
     */
    protected array $deferred = [];

    abstract protected function fetchItem(string $key): ?CacheItemInterface;
    abstract protected function removeItem(string $key): bool;
    abstract protected function saveItem(CacheItemInterface $item): bool;

    public function hasItem(string $key): bool
    {
        $this->assertKey($key);

        if (null === $item = $this->fetchItem($key)) {
            return false;
        }

        if (false === $item->isHit()) {
            $this->removeItem($key);

            return false;
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $this->assertKey($item->getKey());

        if (false === $item->isHit()) {
            return false;
        }

        return $this->saveItem($item);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[] = $item;

        return true;
    }

    public function commit(): bool
    {
        while (null !== $item = array_shift($this->deferred)) {
            if (false === $this->save($item)) {
                return false;
            }
        }

        return true;
    }

    public function getItem(string $key): CacheItemInterface
    {
        $this->assertKey($key);

        return $this->fetchItem($key) ?? new CacheItem($key, null);
    }

    /**
     * @param array<string> $keys
     * @return iterable<CacheItemInterface>
     */
    public function getItems(array $keys = []): iterable
    {
        $items = [];

        foreach ($keys as $key) {
            $items[] = $this->getItem($key);
        }

        return $items;
    }

    public function deleteItem(string $key): bool
    {
        $this->assertKey($key);

        return $this->removeItem($key);
    }

    /**
     * @param array<string> $keys
     * @return bool
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            if (false === $this->deleteItem($key)) {
                return false;
            }
        }

        return true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (false === $this->hasItem($key)) {
            return $default;
        }

        return $this->getItem($key)->get();
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $item = new CacheItem($key, $value);
        $item->expiresAfter($ttl);

        return $this->save($item);
    }

    public function delete(string $key): bool
    {
        return $this->deleteItem($key);
    }

    /**
     * @param iterable<string> $keys
     * @param mixed $default
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $values = [];

        foreach ($this->getItems((array) $keys) as $item) {
            $values[$item->getKey()] = $item->isHit() ? $item->get() : $default;
        }

        return $values;
    }

    /**
     * @param iterable<string, mixed> $values
     * @param null|int|DateInterval $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            if (false === $this->set($key, $value, $ttl)) {
                return false;
            }
        }

        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->deleteItems((array) $keys);
    }

    public function has(string $key): bool
    {
        return $this->hasItem($key);
    }

    protected function assertKey(string $key): void
    {
        if ('' === trim($key)) {
            throw InvalidArgumentException::forInvalidKey();
        }
    }
}
