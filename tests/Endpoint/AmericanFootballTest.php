<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Endpoint\AmericanFootball;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AmericanFootball::class)]
final class AmericanFootballTest extends TestCase
{
    private MockTransport $transport;

    private AmericanFootball $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new AmericanFootball($this->transport, new Enums());
    }

    public function testTotalMatchesExtractsAmericanFootballKey(): void
    {
        $this->transport->setResponse([
            'football' => ['live' => 5, 'total' => 100],
            'american-football' => ['live' => 8, 'total' => 16],
        ]);
        $result = $this->endpoint->totalMatches();

        self::assertSame('/sport/0/event-count', $this->transport->lastEndpoint());
        self::assertSame(['live' => 8, 'total' => 16], $result);
    }

    public function testTotalMatchesDefaultsToEmptyArrayWhenMissing(): void
    {
        $this->transport->setResponse(['football' => ['live' => 5, 'total' => 100]]);

        self::assertSame([], $this->endpoint->totalMatches());
    }

    public function testMatchesByDateWithExplicitDateAndSport(): void
    {
        $this->endpoint->matchesByDate('tennis', '2025-01-31');

        self::assertSame('/sport/tennis/scheduled-tournaments/2025-01-31/page/1', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateDefaultsSportToAmericanFootballAndDateToToday(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->endpoint->matchesByDate();

        self::assertSame("/sport/american-football/scheduled-tournaments/{$today}/page/1", $this->transport->lastEndpoint());
    }

    public function testMatchesByDateRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->matchesByDate('quidditch');
    }

    public function testCategories(): void
    {
        $this->endpoint->categories();

        self::assertSame('/sport/american-football/categories', $this->transport->lastEndpoint());
    }

    public function testTournaments(): void
    {
        $this->endpoint->tournaments(63);

        self::assertSame('/category/63/unique-tournaments', $this->transport->lastEndpoint());
    }

    public function testBestPlayerOfPlayoffs(): void
    {
        $this->endpoint->bestPlayerOfPlayoffs(4424, 9464, 60592);

        self::assertSame(
            '/team/4424/unique-tournament/9464/season/60592/top-players/playoffs',
            $this->transport->lastEndpoint(),
        );
    }

    public function testSeasonGames(): void
    {
        $this->endpoint->seasonGames(9464, 60592);

        self::assertSame('/unique-tournament/9464/season/60592/team-events/total', $this->transport->lastEndpoint());
    }

    public function testTournamentInfo(): void
    {
        $this->endpoint->tournamentInfo(9464, 60592);

        self::assertSame('/unique-tournament/9464/season/60592/info', $this->transport->lastEndpoint());
    }

    public function testRoundHighlights(): void
    {
        $this->endpoint->roundHighlights('US', 9464, 60592, 5);

        self::assertSame(
            '/sport-video-highlights/country/US/unique-tournament/9464/season/60592/round/5',
            $this->transport->lastEndpoint(),
        );
    }

    public function testTeamSeasons(): void
    {
        $this->endpoint->teamSeasons(4424);

        self::assertSame('/team/4424/standings/seasons', $this->transport->lastEndpoint());
    }

    public function testStandings(): void
    {
        $this->endpoint->standings(9464, 60592);

        self::assertSame('/tournament/9464/season/60592/standings/total', $this->transport->lastEndpoint());
    }

    public function testTeamNearGames(): void
    {
        $this->endpoint->teamNearGames(4424);

        self::assertSame('/team/4424/near-events', $this->transport->lastEndpoint());
    }

    public function testTeamPlayerStats(): void
    {
        $this->endpoint->teamPlayerStats(4424, 9464, 60592);

        self::assertSame(
            '/team/4424/unique-tournament/9464/season/60592/player-statistics/regularSeason',
            $this->transport->lastEndpoint(),
        );
    }

    public function testPlayerStats(): void
    {
        $this->endpoint->playerStats(112233, 9464, 60592);

        self::assertSame(
            '/player/112233/unique-tournament/9464/season/60592/statistics/regularSeason',
            $this->transport->lastEndpoint(),
        );
    }
}
