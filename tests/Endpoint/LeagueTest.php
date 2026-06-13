<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Dto\Tournament;
use Nietonchique\SofascoreApiBundle\Endpoint\League;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(League::class)]
final class LeagueTest extends TestCase
{
    private const LEAGUE_ID = 17;

    /**
     * @param array<array-key, mixed> $response
     *
     * @return array{0: League, 1: MockTransport}
     */
    private function league(array $response = []): array
    {
        $transport = new MockTransport($response);

        return [new League($transport, new Enums(), self::LEAGUE_ID), $transport];
    }

    public function testGetLeagueReturnsTournamentDto(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league(['id' => 17, 'name' => 'Premier League', 'slug' => 'premier-league']);
        $result = $league->getLeague();

        self::assertInstanceOf(Tournament::class, $result);
        self::assertSame('Premier League', $result->name);
        self::assertSame('/unique-tournament/17', $transport->lastEndpoint());
    }

    public function testGetSeasonsUnwrapsSeasonsKey(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league(['seasons' => [['id' => 61627, 'year' => '24/25']]]);
        $result = $league->getSeasons();

        self::assertSame([['id' => 61627, 'year' => '24/25']], $result);
        self::assertSame('/unique-tournament/17/seasons', $transport->lastEndpoint());
    }

    public function testGetSeasonsDefaultsToEmptyArray(): void
    {
        /** @var League $league */
        [$league] = $this->league([]);

        self::assertSame([], $league->getSeasons());
    }

    public function testCurrentSeasonReturnsFirst(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league(['seasons' => [['id' => 61627], ['id' => 52186]]]);
        $result = $league->currentSeason();

        self::assertSame(['id' => 61627], $result);
        self::assertSame('/unique-tournament/17/seasons', $transport->lastEndpoint());
    }

    public function testCurrentSeasonReturnsNullWhenEmpty(): void
    {
        /** @var League $league */
        [$league] = $this->league(['seasons' => []]);

        self::assertNull($league->currentSeason());
    }

    public function testGetInfoBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league(['info' => ['goals' => 691]]);
        $result = $league->getInfo(61627);

        self::assertSame(['info' => ['goals' => 691]], $result);
        self::assertSame('/unique-tournament/17/season/61627/info', $transport->lastEndpoint());
    }

    public function testTopPlayersPerGameBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->topPlayersPerGame(61627);

        self::assertSame('/unique-tournament/17/season/61627/top-players-per-game/all/overall', $transport->lastEndpoint());
    }

    public function testGetImageBuildsUrl(): void
    {
        /** @var League $league */
        [$league] = $this->league([]);

        self::assertSame('https://www.sofascore.com/api/v1/unique-tournament/17/image/dark', $league->getImage());
        self::assertSame('https://www.sofascore.com/api/v1/unique-tournament/17/image/light', $league->getImage('light'));
    }

    public function testTopPlayersBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->topPlayers(61627);

        self::assertSame('/unique-tournament/17/season/61627/top-players/overall', $transport->lastEndpoint());
    }

    public function testTopTeamsBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->topTeams(61627);

        self::assertSame('/unique-tournament/17/season/61627/top-teams/overall', $transport->lastEndpoint());
    }

    public function testGetLatestHighlightsBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->getLatestHighlights();

        self::assertSame('/unique-tournament/17/media', $transport->lastEndpoint());
    }

    public function testStandingsBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->standings(61627);

        self::assertSame('/unique-tournament/17/season/61627/standings/total', $transport->lastEndpoint());
    }

    public function testStandingsHomeBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->standingsHome(61627);

        self::assertSame('/unique-tournament/17/season/61627/standings/home', $transport->lastEndpoint());
    }

    public function testStandingsAwayBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->standingsAway(61627);

        self::assertSame('/unique-tournament/17/season/61627/standings/away', $transport->lastEndpoint());
    }

    public function testPlayerOfTheSeasonBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->playerOfTheSeason(61627);

        self::assertSame('/unique-tournament/17/season/61627/player-of-the-season', $transport->lastEndpoint());
    }

    public function testFeaturedGamesBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->featuredGames();

        self::assertSame('/unique-tournaments/17/featured-events', $transport->lastEndpoint());
    }

    public function testTotwRoundsBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->totwRounds(61627);

        self::assertSame('/unique-tournament/17/season/61627/team-of-the-week/rounds', $transport->lastEndpoint());
    }

    public function testTotwBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->totw(61627, 24);

        self::assertSame('/unique-tournament/17/season/61627/team-of-the-week/24', $transport->lastEndpoint());
    }

    public function testRoundsBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->rounds(61627);

        self::assertSame('/unique-tournament/17/season/61627/rounds', $transport->lastEndpoint());
    }

    public function testCurrentRoundUnwrapsNestedRound(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league(['currentRound' => ['round' => 24]]);
        $result = $league->currentRound(61627);

        self::assertSame(24, $result);
        self::assertSame('/unique-tournament/17/season/61627/rounds', $transport->lastEndpoint());
    }

    public function testCurrentRoundReturnsNullWhenMissing(): void
    {
        /** @var League $league */
        [$league] = $this->league([]);

        self::assertNull($league->currentRound(61627));
    }

    public function testFixturesBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->fixtures(61627, 24);

        self::assertSame('/unique-tournament/17/season/61627/round/24', $transport->lastEndpoint());
    }

    public function testNextFixturesFiltersAndSortsAscending(): void
    {
        $response = [
            'seasons' => [['id' => 61627]],
            'currentRound' => ['round' => 24],
            'events' => [
                ['id' => 1, 'status' => ['code' => 0], 'startTimestamp' => 300],
                ['id' => 2, 'status' => ['code' => 100], 'startTimestamp' => 100],
                ['id' => 3, 'status' => ['code' => 0], 'startTimestamp' => 200],
            ],
        ];
        /** @var League $league */
        [$league, $transport] = $this->league($response);
        $result = $league->nextFixtures();

        self::assertNotNull($result);
        self::assertSame([3, 1], array_column($result, 'id'));
        self::assertSame('/unique-tournament/17/season/61627/events/round/24', $transport->lastEndpoint());
    }

    public function testNextFixturesReturnsNullWhenNoneMatch(): void
    {
        $response = [
            'seasons' => [['id' => 61627]],
            'currentRound' => ['round' => 24],
            'events' => [['id' => 1, 'status' => ['code' => 100], 'startTimestamp' => 100]],
        ];
        /** @var League $league */
        [$league] = $this->league($response);

        self::assertNull($league->nextFixtures());
    }

    public function testLastFixturesFiltersAndSortsDescending(): void
    {
        $response = [
            'seasons' => [['id' => 61627]],
            'currentRound' => ['round' => 24],
            'events' => [
                ['id' => 1, 'status' => ['code' => 100], 'startTimestamp' => 100],
                ['id' => 2, 'status' => ['code' => 0], 'startTimestamp' => 999],
                ['id' => 3, 'status' => ['code' => 100], 'startTimestamp' => 300],
            ],
        ];
        /** @var League $league */
        [$league, $transport] = $this->league($response);
        $result = $league->lastFixtures();

        self::assertNotNull($result);
        self::assertSame([3, 1], array_column($result, 'id'));
        self::assertSame('/unique-tournament/17/season/61627/events/round/24', $transport->lastEndpoint());
    }

    public function testLastFixturesFallsBackToPreviousRound(): void
    {
        // No status-100 events anywhere, so both the current and previous round
        // requests yield nothing and the method returns null. Asserting the
        // fallback endpoint (round 23) proves the previous-round branch ran.
        $response = [
            'seasons' => [['id' => 61627]],
            'currentRound' => ['round' => 24],
            'events' => [['id' => 1, 'status' => ['code' => 0], 'startTimestamp' => 100]],
        ];
        /** @var League $league */
        [$league, $transport] = $this->league($response);
        $result = $league->lastFixtures();

        self::assertNull($result);
        self::assertSame('/unique-tournament/17/season/61627/events/round/23', $transport->lastEndpoint());
    }

    public function testCupTreeBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->cupTree(61627);

        self::assertSame('/unique-tournament/17/season/61627/cuptrees', $transport->lastEndpoint());
    }

    public function testCupFixturesPerRoundBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->cupFixturesPerRound(61627, 5);

        self::assertSame('/unique-tournament/17/season/61627/events/round/5/slug/round-5', $transport->lastEndpoint());
    }

    public function testLeagueFixturesPerRoundBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->leagueFixturesPerRound(61627, 5);

        self::assertSame('/unique-tournament/17/season/61627/events/round/5', $transport->lastEndpoint());
    }

    public function testLeaguesBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->leagues(1);

        self::assertSame('/category/1/unique-tournaments', $transport->lastEndpoint());
    }

    public function testLeagueInfoBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->leagueInfo(8, 61627);

        self::assertSame('/unique-tournaments/8/season/61627/info', $transport->lastEndpoint());
    }

    public function testGetLeagueByIdBuildsPath(): void
    {
        /** @var League $league */
        [$league, $transport] = $this->league([]);
        $league->getLeagueById(8);

        self::assertSame('/unique-tournaments/8', $transport->lastEndpoint());
    }
}
