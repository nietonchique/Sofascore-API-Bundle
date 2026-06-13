<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Transport;

use JsonException;
use Nietonchique\SofascoreApiBundle\Exception\ApiBlockedException;
use Nietonchique\SofascoreApiBundle\Exception\ApiException;
use Nietonchique\SofascoreApiBundle\Exception\NotFoundException;
use RuntimeException;

/**
 * Headless-Chrome transport — the PHP counterpart of the Python wrapper's
 * Playwright/Chromium mode. Used as a fallback for Cloudflare-blocked requests.
 *
 * Browser interaction is delegated to a {@see BrowserFetcherInterface} so this
 * class stays unit-testable; the production implementation is
 * {@see ChromeBrowserFetcher} (chrome-php/chrome).
 */
final class ChromeTransport implements TransportInterface
{
    public function __construct(
        private readonly BrowserFetcherInterface $fetcher,
        private readonly string $baseUrl = HttpClientTransport::BASE_URL,
    ) {
    }

    public function get(string $endpoint, array $query = []): array
    {
        $url = $this->baseUrl.$endpoint;
        if ([] !== $query) {
            $url .= '?'.http_build_query($query);
        }

        return $this->fetchJson($url);
    }

    public function getRaw(string $url): array
    {
        return $this->fetchJson($url);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function fetchJson(string $url): array
    {
        try {
            $body = $this->fetcher->fetch($url);
        } catch (RuntimeException $e) {
            throw new ApiException(\sprintf('Headless-Chrome request to "%s" failed: %s', $url, $e->getMessage()), 0, $url, $e);
        }

        try {
            $decoded = json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            // A non-JSON body almost always means a Cloudflare challenge page.
            throw new ApiBlockedException(\sprintf('Headless-Chrome request to "%s" did not return JSON (likely blocked).', $url), 0, $url, $e);
        }

        if (!\is_array($decoded)) {
            throw new ApiException(\sprintf('Headless-Chrome request to "%s" returned a non-object JSON value.', $url), 0, $url);
        }

        $this->guardErrorEnvelope($decoded, $url);

        return $decoded;
    }

    /**
     * SofaScore/Cloudflare answer a blocked request with a 200 page whose body is
     * an error envelope like {@code {"error":{"code":403,"reason":"challenge"}}}.
     * Detect it so the chain transport can react instead of treating it as data.
     *
     * @param array<array-key, mixed> $decoded
     */
    private function guardErrorEnvelope(array $decoded, string $url): void
    {
        $error = $decoded['error'] ?? null;
        if (!\is_array($error)) {
            return;
        }

        $code = $error['code'] ?? null;
        if (!\is_int($code)) {
            return;
        }

        if (403 === $code) {
            throw new ApiBlockedException(\sprintf('"%s" returned a Cloudflare/API challenge (error code 403).', $url), $code, $url);
        }
        if (404 === $code) {
            throw new NotFoundException(\sprintf('Resource "%s" was not found (error code 404).', $url), $code, $url);
        }
        if ($code >= 400) {
            throw new ApiException(\sprintf('"%s" returned API error code %d.', $url, $code), $code, $url);
        }
    }
}
