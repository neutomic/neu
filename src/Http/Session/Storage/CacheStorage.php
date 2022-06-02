<?php

declare(strict_types=1);

namespace Neu\Http\Session\Storage;

use Neu\Cache\CacheInterface;
use Neu\Http\Session\Session;
use Neu\Http\Session\SessionInterface;
use Psl\Json;
use Psl\Ref;
use Psl\SecureRandom;
use Psl\Type;

final class CacheStorage implements StorageInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function write(SessionInterface $session, null|int $ttl = null): string
    {
        $id = $session->getId();
        if ('' === $id || $session->isRegenerated() || $session->hasChanges()) {
            $id = $this->generateIdentifier();
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $this->cache->update($id, static fn(): string => Json\encode($session->all()), $ttl);

        return $id;
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): SessionInterface
    {
        /** @psalm-suppress MissingThrowsDocblock */
        $json = $this->cache->compute($id, static fn(): string => Json\encode([]));
        /** @psalm-suppress MissingThrowsDocblock */
        $data = Json\typed($json, Type\dict(Type\string(), Type\mixed()));

        return new Session($data, $id);
    }

    /**
     * @inheritDoc
     */
    public function flush(string $id): void
    {
        /** @psalm-suppress MissingThrowsDocblock */
        $this->cache->delete($id);
    }

    /**
     * @return non-empty-string
     */
    private function generateIdentifier(): string
    {
        $is_present =
            /**
             * @param non-empty-string $id
             */
            function (string $id): bool {
                /** @var Ref<bool> $reference */
                $reference = new Ref(false);
                $this->cache->compute($id, static function () use ($reference): string {
                    $reference->value = true;

                    return '';
                });

                if ($reference->value) {
                    $this->cache->delete($id);
                }

                return $reference->value;
            };

        do {
            /** @psalm-suppress MissingThrowsDocblock */
            $id = 'session_' . SecureRandom\string(16);
        } while (!$is_present($id));

        return $id;
    }
}
