<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Transport;

use Nietonchique\SofascoreApiBundle\Exception\ApiBlockedException;
use Nietonchique\SofascoreApiBundle\Exception\NotFoundException;
use Nietonchique\SofascoreApiBundle\Transport\ChainTransport;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChainTransport::class)]
final class ChainTransportTest extends TestCase
{
    public function testUsesPrimaryWhenItSucceeds(): void
    {
        $primary = $this->transportReturning(['from' => 'primary']);
        $fallback = $this->transportReturning(['from' => 'fallback']);
        $chain = new ChainTransport($primary, $fallback);

        self::assertSame(['from' => 'primary'], $chain->get('/x'));
    }

    public function testFallsBackOnBlocked(): void
    {
        $primary = $this->createMock(TransportInterface::class);
        $primary->method('get')->willThrowException(new ApiBlockedException('blocked'));
        $fallback = $this->transportReturning(['from' => 'fallback']);

        $chain = new ChainTransport($primary, $fallback);

        self::assertSame(['from' => 'fallback'], $chain->get('/x', ['q' => 'a']));
    }

    public function testDoesNotFallBackOnOtherErrors(): void
    {
        $primary = $this->createMock(TransportInterface::class);
        $primary->method('get')->willThrowException(new NotFoundException('nope'));
        $fallback = $this->createMock(TransportInterface::class);
        $fallback->expects(self::never())->method('get');

        $chain = new ChainTransport($primary, $fallback);

        $this->expectException(NotFoundException::class);
        $chain->get('/x');
    }

    public function testGetRawFallsBack(): void
    {
        $primary = $this->createMock(TransportInterface::class);
        $primary->method('getRaw')->willThrowException(new ApiBlockedException('blocked'));
        $fallback = $this->transportReturning(['raw' => true]);

        $chain = new ChainTransport($primary, $fallback);

        self::assertSame(['raw' => true], $chain->getRaw('https://x.test/y'));
    }

    /**
     * @param array<array-key, mixed> $value
     */
    private function transportReturning(array $value): TransportInterface
    {
        $t = $this->createMock(TransportInterface::class);
        $t->method('get')->willReturn($value);
        $t->method('getRaw')->willReturn($value);

        return $t;
    }
}
