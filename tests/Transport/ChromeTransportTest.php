<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Transport;

use Nietonchique\SofascoreApiBundle\Exception\ApiBlockedException;
use Nietonchique\SofascoreApiBundle\Exception\ApiException;
use Nietonchique\SofascoreApiBundle\Exception\NotFoundException;
use Nietonchique\SofascoreApiBundle\Tests\Support\RecordingBrowserFetcher;
use Nietonchique\SofascoreApiBundle\Transport\BrowserFetcherInterface;
use Nietonchique\SofascoreApiBundle\Transport\ChromeTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ChromeTransport::class)]
final class ChromeTransportTest extends TestCase
{
    public function testDecodesJsonBody(): void
    {
        $transport = new ChromeTransport($this->fetcher('{"id":1,"name":"x"}'), 'https://example.test/api/v1');

        self::assertSame(['id' => 1, 'name' => 'x'], $transport->get('/event/1'));
    }

    public function testBuildsUrlWithQuery(): void
    {
        $fetcher = new RecordingBrowserFetcher('{}');
        $transport = new ChromeTransport($fetcher, 'https://example.test/api/v1');

        $transport->get('/search/all', ['q' => 'arsenal']);

        self::assertSame('https://example.test/api/v1/search/all?q=arsenal', $fetcher->lastUrl);
    }

    public function testNonJsonBodyIsTreatedAsBlocked(): void
    {
        $transport = new ChromeTransport($this->fetcher('<html>Just a moment...</html>'), 'https://example.test/api/v1');

        $this->expectException(ApiBlockedException::class);
        $transport->get('/x');
    }

    public function testFetcherErrorBecomesApiException(): void
    {
        $fetcher = $this->createMock(BrowserFetcherInterface::class);
        $fetcher->method('fetch')->willThrowException(new RuntimeException('chrome down'));
        $transport = new ChromeTransport($fetcher, 'https://example.test/api/v1');

        $this->expectException(ApiException::class);
        $transport->get('/x');
    }

    public function testChallengeErrorEnvelopeIsTreatedAsBlocked(): void
    {
        $transport = new ChromeTransport($this->fetcher('{"error":{"code":403,"reason":"challenge"}}'), 'https://example.test/api/v1');

        $this->expectException(ApiBlockedException::class);
        $transport->get('/x');
    }

    public function testNotFoundErrorEnvelope(): void
    {
        $transport = new ChromeTransport($this->fetcher('{"error":{"code":404,"reason":"not found"}}'), 'https://example.test/api/v1');

        $this->expectException(NotFoundException::class);
        $transport->get('/x');
    }

    public function testGenericErrorEnvelope(): void
    {
        $transport = new ChromeTransport($this->fetcher('{"error":{"code":500}}'), 'https://example.test/api/v1');

        $this->expectException(ApiException::class);
        $transport->get('/x');
    }

    public function testPayloadWithErrorKeyButNoCodeIsReturned(): void
    {
        $transport = new ChromeTransport($this->fetcher('{"error":null,"results":[1,2]}'), 'https://example.test/api/v1');

        self::assertSame(['error' => null, 'results' => [1, 2]], $transport->get('/x'));
    }

    private function fetcher(string $body): BrowserFetcherInterface
    {
        $fetcher = $this->createMock(BrowserFetcherInterface::class);
        $fetcher->method('fetch')->willReturn($body);

        return $fetcher;
    }
}
