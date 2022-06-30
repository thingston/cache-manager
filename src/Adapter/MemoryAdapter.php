<?php

declare(strict_types=1);

namespace Thingston\Cache\Adapter;

use Psr\Cache\CacheItemInterface;

final class MemoryAdapter extends AbstractAdapter
{
    /**
     * @var array<string, CacheItemInterface>
     */
    private array $items = [];

    public function clear(): bool
    {
        $this->items = $this->deferred = [];

        return true;
    }

    protected function fetchItem(string $key): ?CacheItemInterface
    {
        return $this->items[$key] ?? null;
    }

    protected function removeItem(string $key): bool
    {
        if (isset($this->items[$key])) {
            unset($this->items[$key]);

            return true;
        }

        return false;
    }

    protected function saveItem(CacheItemInterface $item): bool
    {
        $this->items[$item->getKey()] = $item;

        return true;
    }
}
