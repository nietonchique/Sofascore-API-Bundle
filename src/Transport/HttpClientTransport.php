<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Transport;

use JsonException;
use Nietonchique\SofascoreApiBundle\Exception\ApiBlockedException;
use Nietonchique\SofascoreApiBundle\Exception\ApiException;
use Nietonchique\SofascoreApiBundle\Exception\NotFoundException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Default transport: a plain HTTP client (Symfony HttpClient) sending a realistic
 * browser header set. Fast and dependency-light; if SofaScore answers 403
 * (Cloudflare) the {@see ChainTransport} falls back to {@see ChromeTransport}.
 */
final class HttpClientTransport implements TransportInterface
{
    public const BASE_URL = 'https://www.sofascore.com/api/v1';

    /**
     * @var array<string, string>
     */
    private const DEFAULT_HEADERS = [
        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept' => 'application/json, text/plain, */*',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Referer' => 'https://www.sofascore.com/',
        'Origin' => 'https://www.sofascore.com',
        'Cache-Control' => 'no-cache',
    ];

    /**
     * @param array<string, string> $headers extra headers merged over the browser defaults
     */
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl = self::BASE_URL,
        private readonly array $headers = [],
    ) {
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request($this->baseUrl.$endpoint, $query);
    }

    public function getRaw(string $url): array
    {
        return $this->request($url, []);
    }

    /**
     * @param array<string, scalar|null> $query
     *
     * @return array<array-key, mixed>
     */
    private function request(string $url, array $query): array
    {
        try {
            $response = $this->httpClient->request('GET', $url, [
                'query' => $query,
                'headers' => [...self::DEFAULT_HEADERS, ...$this->headers],
            ]);
            $status = $response->getStatusCode();
        } catch (HttpClientExceptionInterface $e) {
            throw new ApiException(\sprintf('Request to "%s" failed: %s', $url, $e->getMessage()), 0, $url, $e);
        }

        if (200 === $status) {
            try {
                return $response->toArray(false);
            } catch (HttpClientExceptionInterface|JsonException $e) {
                throw new ApiException(\sprintf('Could not decode JSON from "%s": %s', $url, $e->getMessage()), $status, $url, $e);
            }
        }

        throw match ($status) {
            403 => new ApiBlockedException(\sprintf('Request to "%s" was blocked (HTTP 403).', $url), $status, $url),
            404 => new NotFoundException(\sprintf('Resource "%s" was not found (HTTP 404).', $url), $status, $url),
            default => new ApiException(\sprintf('Request to "%s" failed with HTTP %d.', $url, $status), $status, $url),
        };
    }
}
