<?php

declare(strict_types=1);

namespace Thingston\Cache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;
use Thingston\Cache\Exception\InvalidArgumentException;

final class CacheItem implements CacheItemInterface
{
    private DateTimeInterface $expiresAt;

    public function __construct(private string $key, private mixed $value, int $expiresAfter = 0)
    {
        if ('' === trim($key)) {
            throw InvalidArgumentException::forInvalidKey();
        }

        $this->key = $key;
        $this->value = $value;
        $this->expiresAfter($expiresAfter);
    }

    public function expiresAfter(int|DateInterval|null $time): static
    {
        if (is_int($time)) {
            $this->expiresAt = new DateTime('@' . (time() + $time));
        } elseif ($time instanceof DateInterval) {
            $this->expiresAt = (new DateTime())->add($time);
        } else {
            $this->expiresAt = new DateTime();
        }

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiresAt = $expiration ?? new DateTime();

        return $this;
    }

    public function get(): mixed
    {
        if (false === $this->isHit()) {
            return null;
        }

        return $this->value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function isHit(): bool
    {
        return (new DateTime())->format('Uu') < $this->expiresAt->format('Uu');
    }

    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }
}
