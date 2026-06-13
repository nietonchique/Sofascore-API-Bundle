<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Transport;

use Nietonchique\SofascoreApiBundle\Exception\ApiBlockedException;
use Nietonchique\SofascoreApiBundle\Exception\ApiException;
use Nietonchique\SofascoreApiBundle\Exception\NotFoundException;
use Nietonchique\SofascoreApiBundle\Transport\HttpClientTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(HttpClientTransport::class)]
final class HttpClientTransportTest extends TestCase
{
    public function testGetReturnsDecodedJsonOn200(): void
    {
        $client = new MockHttpClient(new MockResponse('{"results":[{"id":42}]}', ['http_code' => 200]));
        $transport = new HttpClientTransport($client, 'https://example.test/api/v1');

        self::assertSame(['results' => [['id' => 42]]], $transport->get('/search/all'));
    }

    public function testGetBuildsUrlAndQuery(): void
    {
        $seen = null;
        $client = new MockHttpClient(function (string $method, string $url) use (&$seen): MockResponse {
            $seen = $url;

            return new MockResponse('{"ok":true}');
        });
        $transport = new HttpClientTransport($client, 'https://example.test/api/v1');

        $transport->get('/search/all', ['q' => 'arsenal', 'page' => 0]);

        self::assertNotNull($seen);
        self::assertStringContainsString('https://example.test/api/v1/search/all', $seen);
        self::assertStringContainsString('q=arsenal', $seen);
        self::assertStringContainsString('page=0', $seen);
    }

    public function testSendsBrowserHeaders(): void
    {
        $captured = [];
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $headers = $options['headers'] ?? [];
            if (\is_array($headers)) {
                foreach ($headers as $line) {
                    $captured[] = \is_string($line) ? $line : '';
                }
            }

            return new MockResponse('{}');
        });
        $transport = new HttpClientTransport($client, 'https://example.test/api/v1');

        $transport->get('/x');

        $joined = implode("\n", $captured);
        self::assertStringContainsString('sofascore.com', $joined);
        self::assertStringContainsString('Mozilla/5.0', $joined);
    }

    public function test403ThrowsApiBlocked(): void
    {
        $client = new MockHttpClient(new MockResponse('blocked', ['http_code' => 403]));
        $transport = new HttpClientTransport($client, 'https://example.test/api/v1');

        $this->expectException(ApiBlockedException::class);
        $transport->get('/x');
    }

    public function test404ThrowsNotFound(): void
    {
        $client = new MockHttpClient(new MockResponse('nope', ['http_code' => 404]));
        $transport = new HttpClientTransport($client, 'https://example.test/api/v1');

        $this->expectException(NotFoundException::class);
        $transport->get('/x');
    }

    public function test500ThrowsApiException(): void
    {
        $client = new MockHttpClient(new MockResponse('err', ['http_code' => 500]));
        $transport = new HttpClientTransport($client, 'https://example.test/api/v1');

        try {
            $transport->get('/x');
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame(500, $e->getStatusCode());
        }
    }

    public function testUndecodableBodyThrowsApiException(): void
    {
        $client = new MockHttpClient(new MockResponse('<html>not json</html>', ['http_code' => 200]));
        $transport = new HttpClientTransport($client, 'https://example.test/api/v1');

        $this->expectException(ApiException::class);
        $transport->get('/x');
    }

    public function testGetRawUsesAbsoluteUrl(): void
    {
        $seen = null;
        $client = new MockHttpClient(function (string $method, string $url) use (&$seen): MockResponse {
            $seen = $url;

            return new MockResponse('{"a":1}');
        });
        $transport = new HttpClientTransport($client, 'https://example.test/api/v1');

        self::assertSame(['a' => 1], $transport->getRaw('https://other.test/raw'));
        self::assertSame('https://other.test/raw', $seen);
    }
}
