<?php

declare(strict_types=1);

namespace Neu\Http\Session;

use Psl\Iter;

final class Session implements SessionInterface
{
    public const  SESSION_AGE_KEY = '__SESSION_AGE__';

    private bool $isRegenerated = false;

    /**
     * original data of the session.
     *
     * @var array<string, mixed>
     */
    private readonly array $originalData;

    /**
     * Lifetime of the session cookie.
     */
    private int $age = 0;

    private bool $flushed = false;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private array $data,
        private readonly string $id = '',
    ) {
        $this->originalData = $this->data;

        if (Iter\contains_key($this->data, static::SESSION_AGE_KEY)) {
            $this->age = (int)$this->data[static::SESSION_AGE_KEY];
        }
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return Iter\contains_key($this->data, $key);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function add(string $key, mixed $value): static
    {
        if (!Iter\contains_key($this->data, $key)) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        if (!Iter\contains_key($this->data, $key)) {
            throw Exception\UnavailableItemException::for($key);
        }

        return $this->data[$key];
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): static
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function clear(): static
    {
        $this->data = [];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flush(): static
    {
        $this->data = [];
        $this->flushed = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasChanges(): bool
    {
        if ($this->isRegenerated()) {
            return true;
        }

        return $this->data !== $this->originalData;
    }

    /**
     * @inheritDoc
     */
    public function isRegenerated(): bool
    {
        return $this->isRegenerated;
    }

    /**
     * @inheritDoc
     */
    public function isFlushed(): bool
    {
        return $this->flushed;
    }

    /**
     * @inheritDoc
     */
    public function regenerate(): static
    {
        $session = clone $this;
        $session->isRegenerated = true;

        return $session;
    }

    /**
     * @inheritDoc
     */
    public function expireAfter(int $duration): static
    {
        $this->set(static::SESSION_AGE_KEY, $duration);
        $this->age = $duration;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function age(): int
    {
        return $this->age;
    }
}
