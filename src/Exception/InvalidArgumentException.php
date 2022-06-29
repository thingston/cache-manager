<?php

declare(strict_types=1);

namespace Thingston\Cache\Exception;

use Psr\Cache\CacheItemInterface;

class InvalidArgumentException extends \InvalidArgumentException implements CacheExceptionInterface
{
    public static function forInvalidKey(): self
    {
        return new self('Cache item key must be a non-empty string.');
    }

    public static function forInvalidName(string $name): self
    {
        return new self(sprintf('Invalid pool name "%s",', $name));
    }

    public static function forInvalidConfig(string $name): self
    {
        return new self(sprintf('Invalid config type for pool "%s",', $name));
    }

    public static function forInvalidAdapter(string $name): self
    {
        return new self(sprintf('Invalid adapter type for pool "%s",', $name));
    }

    public static function forInvalidArguments(string $name): self
    {
        return new self(sprintf('Invalid arguments type for pool "%s",', $name));
    }

    public static function forInvalidDirectory(string $directory): self
    {
        return new self(sprintf('Argument "%s" isn\'t a valid directory name,', $directory));
    }

    public static function forInvalidFile(string $file): self
    {
        return new self(sprintf('Argument "%s" isn\'t a writable file,', $file));
    }

    public static function forInvalidItem(string $key): self
    {
        return new self(sprintf(
            'Stored value for key "%s" isn\'t an instance of "%s",',
            $key,
            CacheItemInterface::class
        ));
    }
}
