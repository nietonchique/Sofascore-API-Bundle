<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Support;

use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * In-memory transport double. Records every request and returns a preset
 * response, so endpoint methods can be asserted on (a) the path/query they
 * build and (b) the transform they apply to the response.
 */
final class MockTransport implements TransportInterface
{
    /**
     * @var list<array{endpoint: string, query: array<string, scalar|null>}>
     */
    public array $calls = [];

    /**
     * @param array<array-key, mixed> $response
     */
    public function __construct(private array $response = [])
    {
    }

    /**
     * @param array<array-key, mixed> $response
     */
    public function setResponse(array $response): void
    {
        $this->response = $response;
    }

    public function get(string $endpoint, array $query = []): array
    {
        $this->calls[] = ['endpoint' => $endpoint, 'query' => $query];

        return $this->response;
    }

    public function getRaw(string $url): array
    {
        $this->calls[] = ['endpoint' => $url, 'query' => []];

        return $this->response;
    }

    public function lastEndpoint(): ?string
    {
        $last = end($this->calls);

        return false === $last ? null : $last['endpoint'];
    }

    /**
     * @return array<string, scalar|null>
     */
    public function lastQuery(): array
    {
        $last = end($this->calls);

        return false === $last ? [] : $last['query'];
    }

    public function callCount(): int
    {
        return \count($this->calls);
    }
}
