<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Transport\Decorator;

use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use Nietonchique\SofascoreApiBundle\Transport\Decorator\CachingTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

#[CoversClass(CachingTransport::class)]
final class CachingTransportTest extends TestCase
{
    public function testSecondIdenticalCallIsServedFromCache(): void
    {
        $inner = new MockTransport(['v' => 1]);
        $cache = new CachingTransport($inner, new ArrayAdapter(), 300);

        self::assertSame(['v' => 1], $cache->get('/x', ['a' => 1]));
        self::assertSame(['v' => 1], $cache->get('/x', ['a' => 1]));

        self::assertSame(1, $inner->callCount(), 'inner transport should be hit only once');
    }

    public function testDifferentQueryIsNotCached(): void
    {
        $inner = new MockTransport(['v' => 1]);
        $cache = new CachingTransport($inner, new ArrayAdapter(), 300);

        $cache->get('/x', ['a' => 1]);
        $cache->get('/x', ['a' => 2]);

        self::assertSame(2, $inner->callCount());
    }

    public function testGetRawIsCached(): void
    {
        $inner = new MockTransport(['raw' => true]);
        $cache = new CachingTransport($inner, new ArrayAdapter(), 300);

        $cache->getRaw('https://x.test/y');
        $cache->getRaw('https://x.test/y');

        self::assertSame(1, $inner->callCount());
    }
}
