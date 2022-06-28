<?php

declare(strict_types=1);

namespace Thingston\Cache\Adapter;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Thingston\Cache\CacheItem;
use Thingston\Cache\Exception\InvalidArgumentException;

final class MemoryAdapter implements CacheItemPoolInterface
{
    /**
     * @var array<string, CacheItemInterface>
     */
    private array $items = [];

    public function clear(): bool
    {
        $this->items = [];

        return true;
    }

    public function commit(): bool
    {
        return true;
    }

    public function deleteItem(string $key): bool
    {
        if ('' === $key) {
            throw InvalidArgumentException::forInvalidKey();
        }

        if (array_key_exists($key, $this->items)) {
            unset($this->items[$key]);

            return true;
        }

        return false;
    }

    /**
     * @param array<string> $keys
     * @return bool
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function getItem(string $key): CacheItemInterface
    {
        if ('' === $key) {
            throw InvalidArgumentException::forInvalidKey();
        }

        return $this->items[$key] ?? new CacheItem($key, null);
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

    public function hasItem(string $key): bool
    {
        if ('' === $key) {
            throw InvalidArgumentException::forInvalidKey();
        }

        if (false === array_key_exists($key, $this->items)) {
            return false;
        }

        if (false === $this->items[$key]->isHit()) {
            $this->deleteItem($key);

            return false;
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        if (false === $item->isHit()) {
            return false;
        }

        $this->items[$item->getKey()] = $item;

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->save($item);
    }
}
