<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Transport\Decorator;

use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use Nietonchique\SofascoreApiBundle\Transport\Decorator\RateLimitedTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

#[CoversClass(RateLimitedTransport::class)]
final class RateLimitedTransportTest extends TestCase
{
    public function testPassesThroughWhenTokensAvailable(): void
    {
        $inner = new MockTransport(['ok' => true]);
        $transport = new RateLimitedTransport($inner, $this->limiter(100));

        self::assertSame(['ok' => true], $transport->get('/x'));
        self::assertSame(['ok' => true], $transport->getRaw('https://x.test/y'));
        self::assertSame(2, $inner->callCount());
    }

    private function limiter(int $limit): \Symfony\Component\RateLimiter\LimiterInterface
    {
        $factory = new RateLimiterFactory(
            [
                'id' => 'test',
                'policy' => 'token_bucket',
                'limit' => $limit,
                'rate' => ['interval' => '1 minute', 'amount' => $limit],
            ],
            new InMemoryStorage(),
        );

        return $factory->create();
    }
}
