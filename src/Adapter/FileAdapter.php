<?php

declare(strict_types=1);

namespace Thingston\Cache\Adapter;

use Psr\Cache\CacheItemInterface;
use Thingston\Cache\Exception\InvalidArgumentException;
use Throwable;

final class FileAdapter extends AbstractAdapter
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        if (null === $directory) {
            $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'thingston-cache';
        }

        if (DIRECTORY_SEPARATOR !== substr($directory, 0, 1)) {
            $directory = getcwd() . DIRECTORY_SEPARATOR . $directory;
        }

        if (false === is_dir($directory)) {
            $this->createDirectory($directory);
        }

        if (false === is_writable($directory)) {
            throw InvalidArgumentException::forInvalidDirectory($directory);
        }

        $this->directory = realpath($directory) ?: $directory;
    }

    public function clear(): bool
    {
        $this->emptyDirectory($this->directory);

        return true;
    }

    private function createDirectory(string $directory): void
    {
        try {
            if (false === mkdir($directory, 0777, true)) {
                throw InvalidArgumentException::forInvalidDirectory($directory);
            }
        } catch (Throwable $e) {
            throw new InvalidArgumentException(sprintf('Unable to create directory "%s".', $directory), 0, $e);
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
                continue;
            }

            $this->removeFile($path);
        }

        $dir->close();

        if ($remove) {
            rmdir($directory);
        }
    }

    private function removeFile(string $file): void
    {
        unlink($file);
    }

    private function createFileName(string $key): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . hash('crc32', $key);
    }

    protected function fetchItem(string $key): ?CacheItemInterface
    {
        $file = $this->createFileName($key);

        if (false === file_exists($file) || false === $contents = file_get_contents($file)) {
            return null;
        }

        try {
            $item = unserialize($contents);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(sprintf('Invalid content for key "%s".', $key), 0, $e);
        }

        if (false === $item instanceof CacheItemInterface) {
            $this->removeFile($file);

            return null;
        }

        return $item;
    }

    protected function removeItem(string $key): bool
    {
        $file = $this->createFileName($key);

        if (false === file_exists($file) || false === is_writable($file)) {
            return false;
        }

        $this->removeFile($file);

        return true;
    }

    protected function saveItem(CacheItemInterface $item): bool
    {
        $file = $this->createFileName($item->getKey());

        try {
            if (false === file_put_contents($file, serialize($item))) {
                return false;
            }
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }
}
