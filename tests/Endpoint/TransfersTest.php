<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Endpoint\Transfers;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Transfers::class)]
final class TransfersTest extends TestCase
{
    private MockTransport $transport;

    private Transfers $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Transfers($this->transport, new Enums());
    }

    public function testGetTransferFeedDefaults(): void
    {
        $this->endpoint->getTransferFeed();

        self::assertSame('/transfer', $this->transport->lastEndpoint());
        self::assertSame([
            'page' => 1,
            'sort' => '-transferDate',
            'minAge' => 15,
            'maxAge' => 50,
        ], $this->transport->lastQuery());
    }

    public function testGetTransferFeedMapsSortBy(): void
    {
        $this->endpoint->getTransferFeed(sortBy: 'FOLLOWERS');

        self::assertSame('-userCount', $this->transport->lastQuery()['sort']);
    }

    public function testGetTransferFeedClampsAges(): void
    {
        $this->endpoint->getTransferFeed(minAge: 5, maxAge: 99);

        $query = $this->transport->lastQuery();
        self::assertSame(15, $query['minAge']);
        self::assertSame(50, $query['maxAge']);
    }

    public function testGetTransferFeedAppliesOptionalFilters(): void
    {
        $this->endpoint->getTransferFeed(
            page: 2,
            sortBy: 'transferfee',
            nationality: 'dza',
            uniqueTournamentId: 17,
            position: 'fw',
            followersCount: 5000,
        );

        self::assertSame('/transfer', $this->transport->lastEndpoint());
        self::assertSame([
            'page' => 2,
            'sort' => '-transferFee',
            'minAge' => 15,
            'maxAge' => 50,
            'nationality' => 'DZA',
            'uniqueTournamentId' => 17,
            'position' => 'FW',
            'followersCount' => 1000,
        ], $this->transport->lastQuery());
    }

    public function testGetTransferFeedRejectsInvalidSortBy(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->getTransferFeed(sortBy: 'price');
    }

    public function testGetTransferFeedRejectsInvalidPosition(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->getTransferFeed(position: 'XX');
    }
}
