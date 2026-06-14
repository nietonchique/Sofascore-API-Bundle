<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 * Pre-configured HTTP client for downloading SofaScore binary assets (team crests,
 * manager photos, etc.). Decorates the bundle's inner {@see HttpClientInterface}
 * with the browser fingerprint required to avoid Cloudflare 403s on image URLs.
 *
 * It is intentionally separate from {@see TransportInterface}: transport deals with
 * JSON API responses, while this client returns raw Symfony responses and lets the
 * caller read bytes / headers / status code.
 */
final readonly class ImageClient implements HttpClientInterface
{
    /**
     * @var array<string, string>
     */
    private const DEFAULT_HEADERS = [
        'Accept' => '*/*',
        'Accept-Language' => 'en-US,en;q=0.9,ru;q=0.8,ka;q=0.7',
        'Cache-Control' => 'no-cache',
        'DNT' => '1',
        'Pragma' => 'no-cache',
        'Priority' => 'u=1, i',
        'Referer' => 'https://www.sofascore.com/sr',
        'Origin' => 'https://www.sofascore.com',
        'sec-ch-ua' => '"Chromium";v="148", "Vivaldi";v="8.0", "Not/A)Brand";v="99"',
        'sec-ch-ua-mobile' => '?0',
        'sec-ch-ua-platform' => '"Linux"',
        'sec-fetch-dest' => 'empty',
        'sec-fetch-mode' => 'cors',
        'sec-fetch-site' => 'same-origin',
        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
    ];

    private readonly string $requestedWith;

    /**
     * @param array<string, string> $headers extra headers merged over the defaults
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl = 'https://www.sofascore.com/api/v1',
        private array $headers = [],
        ?string $requestedWith = null,
    ) {
        $this->requestedWith = $requestedWith ?? bin2hex(random_bytes(3));
    }

    public function teamCrestUrl(int $teamId): string
    {
        return \sprintf('%s/team/%d/image', $this->baseUrl, $teamId);
    }

    /**
     * @param array<mixed, mixed> $options
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $extraHeaders = $options['headers'] ?? [];
        if (!\is_array($extraHeaders)) {
            $extraHeaders = [];
        }

        $options['headers'] = [
            ...self::DEFAULT_HEADERS,
            'X-Requested-With' => $this->requestedWith,
            ...$this->headers,
            ...$extraHeaders,
        ];

        return $this->httpClient->request($method, $url, $options);
    }

    public function stream(ResponseInterface|iterable $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->httpClient->stream($responses, $timeout);
    }

    /**
     * @param array<mixed, mixed> $options
     */
    public function withOptions(array $options): static
    {
        return new self(
            $this->httpClient->withOptions($options),
            $this->baseUrl,
            $this->headers,
            $this->requestedWith,
        );
    }
}
