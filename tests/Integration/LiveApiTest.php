<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Integration;

use HeadlessChromium\BrowserFactory;
use Nietonchique\SofascoreApiBundle\Exception\ApiBlockedException;
use Nietonchique\SofascoreApiBundle\Transport\ChromeBrowserFetcher;
use Nietonchique\SofascoreApiBundle\Transport\ChromeTransport;
use Nietonchique\SofascoreApiBundle\Transport\HttpClientTransport;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Live tests against the real SofaScore API. Excluded from the default suite
 * (run with `phpunit --group network`).
 *
 * SofaScore sits behind Cloudflare; datacenter / flagged IPs are frequently
 * answered with an HTTP 403 "challenge". These tests therefore verify the real
 * end-to-end contract — a parseable response when reachable — and skip (rather
 * than fail) when the current IP is blocked, which is an environment condition,
 * not a bug in the bundle.
 */
#[Group('network')]
final class LiveApiTest extends TestCase
{
    public function testSearchAllViaHttpClient(): void
    {
        $this->assertReachableOrSkip(new HttpClientTransport(HttpClient::create()));
    }

    public function testSearchAllViaChrome(): void
    {
        if (!class_exists(BrowserFactory::class)) {
            self::markTestSkipped('chrome-php/chrome is not installed.');
        }

        $this->assertReachableOrSkip(new ChromeTransport(new ChromeBrowserFetcher()));
    }

    private function assertReachableOrSkip(TransportInterface $transport): void
    {
        try {
            $response = $transport->get('/search/all', ['q' => 'arsenal', 'page' => 0]);
        } catch (ApiBlockedException $e) {
            self::markTestSkipped('SofaScore is Cloudflare-blocking this IP: '.$e->getMessage());
        }

        self::assertArrayHasKey('results', $response);
        self::assertIsArray($response['results']);
        self::assertNotEmpty($response['results'], 'Expected at least one search result for "arsenal".');
    }
}
