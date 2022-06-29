<?php

declare(strict_types=1);

namespace Thingston\Cache\Adapter;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Thingston\Cache\CacheItem;
use Thingston\Cache\Exception\InvalidArgumentException;

final class FileAdapter implements CacheItemPoolInterface
{
    private string $directory;

    /**
     * @var array<CacheItemInterface>
     */
    private array $deferred = [];

    public function __construct(?string $directory = null)
    {
        if (null === $directory) {
            $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'thingston-cache';
        }

        if (false === is_dir($directory)) {
            $this->createDirectory($directory);
        }

        if (false === is_writable($directory)) {
            throw InvalidArgumentException::forInvalidDirectory($directory);
        }

        $this->directory = $directory;
    }

    private function createDirectory(string $directory): void
    {
        if (false === mkdir($directory, 0777, true)) {
            throw InvalidArgumentException::forInvalidDirectory($directory);
        }
    }

    private function emptyDirectory(string $directory, bool $remove = false): void
    {
        if (false === is_dir($directory) || false === is_writable($directory) || false === $dir = dir($directory)) {
            throw InvalidArgumentException::forInvalidDirectory($directory);
        }

        while (false !== $entry = $dir->read()) {
            if (in_array($entry, ['.', '..'])) {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                $this->emptyDirectory($path, true);

                if ($remove) {
                    rmdir($path);
                }

                continue;
            }

            $this->removeFile($path);
        }

        $dir->close();
    }

    private function removeFile(string $file): void
    {
        if (false === unlink($file)) {
            throw InvalidArgumentException::forInvalidFile($file);
        }
    }

    public function clear(): bool
    {
        $this->emptyDirectory($this->directory);

        return true;
    }

    public function commit(): bool
    {
        while (null !== $item = array_shift($this->deferred)) {
            $this->save($item);
        }

        return true;
    }

    private function createFileName(string $key): string
    {
        if ('' === $key) {
            throw InvalidArgumentException::forInvalidKey();
        }

        return $this->directory . DIRECTORY_SEPARATOR . hash('crc32', $key);
    }

    public function deleteItem(string $key): bool
    {
        $file = $this->createFileName($key);

        if (file_exists($file)) {
            $this->removeFile($file);
        }

        return true;
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
        $file = $this->createFileName($key);

        if (false === file_exists($file) || false === $contents = file_get_contents($file)) {
            return new CacheItem($key, null);
        }

        $item = unserialize($contents);

        if (false === $item instanceof CacheItemInterface) {
            $this->deleteItem($key);
            throw InvalidArgumentException::forInvalidItem($key);
        }

        return $item;
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
        $file = $this->createFileName($key);

        if (false === file_exists($file)) {
            return false;
        }

        if (false === $this->getItem($key)->isHit()) {
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

        $file = $this->createFileName($item->getKey());

        return (bool) file_put_contents($file, serialize($item));
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[] = $item;

        return true;
    }
}
