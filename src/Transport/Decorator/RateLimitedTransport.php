<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Transport\Decorator;

use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;
use Symfony\Component\RateLimiter\LimiterInterface;

/**
 * Opt-in rate-limiting decorator. Blocks (waits) until a token is available
 * before each request, to stay polite to SofaScore and avoid bans. Disabled by
 * default; backed by a Symfony RateLimiter limiter.
 */
final class RateLimitedTransport implements TransportInterface
{
    public function __construct(
        private readonly TransportInterface $inner,
        private readonly LimiterInterface $limiter,
    ) {
    }

    public function get(string $endpoint, array $query = []): array
    {
        $this->limiter->reserve(1)->wait();

        return $this->inner->get($endpoint, $query);
    }

    public function getRaw(string $url): array
    {
        $this->limiter->reserve(1)->wait();

        return $this->inner->getRaw($url);
    }
}
