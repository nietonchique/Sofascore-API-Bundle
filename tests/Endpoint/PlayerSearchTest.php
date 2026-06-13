<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Endpoint\PlayerSearch;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlayerSearch::class)]
final class PlayerSearchTest extends TestCase
{
    public function testSearchPlayerBuildsPath(): void
    {
        $transport = new MockTransport(['players' => []]);
        $result = (new PlayerSearch($transport, new Enums(), 'messi'))->searchPlayer();

        self::assertSame(['players' => []], $result);
        self::assertSame('/search/players/messi', $transport->lastEndpoint());
    }

    public function testSearchPlayerLowercasesAndEncodesSpaces(): void
    {
        $transport = new MockTransport(['players' => []]);
        (new PlayerSearch($transport, new Enums(), 'Cole Palmer'))->searchPlayer();

        self::assertSame('/search/players/cole%20palmer', $transport->lastEndpoint());
    }
}
