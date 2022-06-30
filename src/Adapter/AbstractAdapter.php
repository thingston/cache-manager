<?php

declare(strict_types=1);

namespace Thingston\Cache\Adapter;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Thingston\Cache\CacheItem;
use Thingston\Cache\Exception\InvalidArgumentException;

abstract class AbstractAdapter implements CacheItemPoolInterface
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

    protected function assertKey(string $key): void
    {
        if ('' === trim($key)) {
            throw InvalidArgumentException::forInvalidKey();
        }
    }
}
