<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * Base class for every endpoint group. Holds the transport and the enum helper
 * and exposes small request helpers so the concrete classes read like a thin,
 * faithful translation of the Python wrapper.
 */
abstract class AbstractEndpoint
{
    public function __construct(
        protected readonly TransportInterface $transport,
        protected readonly Enums $enums,
    ) {
    }

    /**
     * @param array<string, scalar|null> $query
     *
     * @return array<array-key, mixed>
     */
    protected function get(string $endpoint, array $query = []): array
    {
        return $this->transport->get($endpoint, $query);
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getRaw(string $url): array
    {
        return $this->transport->getRaw($url);
    }

    /**
     * Current date as {@code Y-m-d}, used as a default for "by date" endpoints
     * (mirrors the Python {@code datetime.datetime.now().strftime('%Y-%m-%d')}).
     */
    protected function today(): string
    {
        return (new DateTimeImmutable())->format('Y-m-d');
    }
}
