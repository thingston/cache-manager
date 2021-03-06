<?php

declare(strict_types=1);

namespace Thingston\Cache\Exception;

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
}
