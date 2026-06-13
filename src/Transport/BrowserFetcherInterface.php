<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Transport;

use RuntimeException;

/**
 * Thin seam over a real browser so {@see ChromeTransport} can be unit-tested
 * without a Chromium binary.
 */
interface BrowserFetcherInterface
{
    /**
     * Navigate to $url and return the raw response body (expected to be JSON text).
     *
     * @throws RuntimeException if the page cannot be fetched
     */
    public function fetch(string $url): string;
}
