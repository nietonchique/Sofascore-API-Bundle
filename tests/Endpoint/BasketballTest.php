<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Endpoint\Basketball;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Basketball::class)]
final class BasketballTest extends TestCase
{
    private MockTransport $transport;

    private Basketball $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Basketball($this->transport, new Enums());
    }

    public function testTotalGamesExtractsBasketballKey(): void
    {
        $this->transport->setResponse([
            'football' => ['live' => 5, 'total' => 100],
            'basketball' => ['live' => 21, 'total' => 270],
        ]);
        $result = $this->endpoint->totalGames();

        self::assertSame('/sport/0/event-count', $this->transport->lastEndpoint());
        self::assertSame(['live' => 21, 'total' => 270], $result);
    }

    public function testTotalGamesDefaultsToEmptyArrayWhenMissing(): void
    {
        $this->transport->setResponse(['football' => ['live' => 5, 'total' => 100]]);
        $result = $this->endpoint->totalGames();

        self::assertSame([], $result);
    }

    public function testLiveGames(): void
    {
        $this->endpoint->liveGames();

        self::assertSame('/sport/basketball/events/live', $this->transport->lastEndpoint());
    }

    public function testGamesByDateWithExplicitDateAndSport(): void
    {
        $this->endpoint->gamesByDate('ice-hockey', '2025-01-31');

        self::assertSame('/sport/ice-hockey/scheduled-tournaments/2025-01-31/page/1', $this->transport->lastEndpoint());
    }

    public function testGamesByDateDefaultsSportToBasketballAndDateToToday(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->endpoint->gamesByDate();

        self::assertSame("/sport/basketball/scheduled-tournaments/{$today}/page/1", $this->transport->lastEndpoint());
    }

    public function testGamesByDateNormalisesSportSlug(): void
    {
        $this->endpoint->gamesByDate('Ice Hockey', '2025-01-31');

        self::assertSame('/sport/ice-hockey/scheduled-tournaments/2025-01-31/page/1', $this->transport->lastEndpoint());
    }

    public function testGamesByDateRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->gamesByDate('quidditch');
    }

    public function testPlayerRatings(): void
    {
        $this->endpoint->playerRatings(1, 2, 3);

        self::assertSame('/player/1/unique-tournament/2/season/3/ratings', $this->transport->lastEndpoint());
    }

    public function testPlayerSeasons(): void
    {
        $this->endpoint->playerSeasons(7);

        self::assertSame('/player/7/statistics/seasons', $this->transport->lastEndpoint());
    }

    public function testPlayerStats(): void
    {
        $this->endpoint->playerStats(7, 132);

        self::assertSame('/player/7/unique-tournament/132/statistics/regularSeason', $this->transport->lastEndpoint());
    }

    public function testTopPlayersPerGame(): void
    {
        $this->endpoint->topPlayersPerGame(132, 65360);

        self::assertSame('/unique-tournament/132/season/65360/top-players-per-game/all/regularSeason', $this->transport->lastEndpoint());
    }

    public function testTopPlayersPerSeason(): void
    {
        $this->endpoint->topPlayersPerSeason(132, 65360);

        self::assertSame('/unique-tournament/132/season/65360/top-players/regularSeason', $this->transport->lastEndpoint());
    }

    public function testTopTeamsPerSeason(): void
    {
        $this->endpoint->topTeamsPerSeason(132, 65360);

        self::assertSame('/unique-tournament/132/season/65360/top-teams/regularSeason', $this->transport->lastEndpoint());
    }
}
