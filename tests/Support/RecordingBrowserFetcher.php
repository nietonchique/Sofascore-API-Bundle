<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Support;

use Nietonchique\SofascoreApiBundle\Transport\BrowserFetcherInterface;

/**
 * Browser-fetcher double that records the requested URL and returns a preset body.
 */
final class RecordingBrowserFetcher implements BrowserFetcherInterface
{
    public ?string $lastUrl = null;

    public function __construct(private readonly string $body = '{}')
    {
    }

    public function fetch(string $url): string
    {
        $this->lastUrl = $url;

        return $this->body;
    }
}
