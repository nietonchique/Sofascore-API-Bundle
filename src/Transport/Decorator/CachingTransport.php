<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Transport\Decorator;

use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Opt-in PSR-6 caching decorator. Caches successful responses keyed by the
 * endpoint/URL and query string for a configurable TTL. Disabled by default.
 */
final class CachingTransport implements TransportInterface
{
    public function __construct(
        private readonly TransportInterface $inner,
        private readonly CacheItemPoolInterface $cache,
        private readonly int $ttl = 300,
    ) {
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->cached('get|'.$endpoint.'|'.http_build_query($query), fn (): array => $this->inner->get($endpoint, $query));
    }

    public function getRaw(string $url): array
    {
        return $this->cached('raw|'.$url, fn (): array => $this->inner->getRaw($url));
    }

    /**
     * @param callable(): array<array-key, mixed> $producer
     *
     * @return array<array-key, mixed>
     */
    private function cached(string $rawKey, callable $producer): array
    {
        $item = $this->cache->getItem(self::key($rawKey));
        if ($item->isHit()) {
            /** @var array<array-key, mixed> $hit */
            $hit = $item->get();

            return $hit;
        }

        $value = $producer();
        $item->set($value)->expiresAfter($this->ttl);
        $this->cache->save($item);

        return $value;
    }

    private static function key(string $rawKey): string
    {
        return 'sofascore_'.hash('xxh128', $rawKey);
    }
}
