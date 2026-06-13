<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Transport;

use Nietonchique\SofascoreApiBundle\Exception\ApiBlockedException;

/**
 * Tries a primary transport and, only when it is blocked (HTTP 403 / Cloudflare),
 * retries the request through a fallback transport.
 *
 * Default wiring: primary = {@see HttpClientTransport}, fallback = {@see ChromeTransport}.
 */
final class ChainTransport implements TransportInterface
{
    public function __construct(
        private readonly TransportInterface $primary,
        private readonly TransportInterface $fallback,
    ) {
    }

    public function get(string $endpoint, array $query = []): array
    {
        try {
            return $this->primary->get($endpoint, $query);
        } catch (ApiBlockedException) {
            return $this->fallback->get($endpoint, $query);
        }
    }

    public function getRaw(string $url): array
    {
        try {
            return $this->primary->getRaw($url);
        } catch (ApiBlockedException) {
            return $this->fallback->getRaw($url);
        }
    }
}
