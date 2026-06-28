<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Endpoint\MatchEndpoint;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MatchEndpoint::class)]
final class MatchEndpointTest extends TestCase
{
    private MockTransport $transport;

    private MatchEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new MatchEndpoint($this->transport, new Enums(), 12345);
    }

    public function testTotalGames(): void
    {
        $this->transport->setResponse(['football' => ['live' => 21, 'total' => 270]]);
        $result = $this->endpoint->totalGames();

        self::assertSame('/sport/0/event-count', $this->transport->lastEndpoint());
        self::assertSame(['live' => 21, 'total' => 270], $result);
    }

    public function testTotalGamesDefaultsToEmptyArray(): void
    {
        $this->transport->setResponse([]);

        self::assertSame([], $this->endpoint->totalGames());
    }

    public function testLiveGames(): void
    {
        $this->endpoint->liveGames();

        self::assertSame('/sport/football/events/live', $this->transport->lastEndpoint());
    }

    public function testGamesByDateWithExplicitDate(): void
    {
        $this->endpoint->gamesByDate('Football', '2025-01-31');

        self::assertSame('/sport/football/scheduled-tournaments/2025-01-31/page/1', $this->transport->lastEndpoint());
    }

    public function testScheduledTournamentsByDateUsesCurrentCalendarEndpoint(): void
    {
        $this->endpoint->scheduledTournamentsByDate('Football', '2026-06-28', 3);

        self::assertSame('/sport/football/scheduled-tournaments/2026-06-28/page/3', $this->transport->lastEndpoint());
    }

    public function testGamesByDateFlattensCurrentScheduledTournamentEvents(): void
    {
        $this->transport->setResponses([
            [
                'scheduled' => [
                    [
                        'tournament' => [
                            'id' => 3948,
                            'uniqueTournament' => ['id' => 16],
                        ],
                    ],
                    [
                        'tournament' => ['id' => 54661],
                    ],
                    [
                        'events' => [
                            ['id' => 15186734, 'slug' => 'inline-event'],
                        ],
                    ],
                ],
                'hasNextPage' => true,
            ],
            ['events' => [
                ['id' => 15186734, 'slug' => 'duplicate-event'],
                ['id' => 15186747, 'slug' => 'world-cup-event'],
            ]],
            ['events' => [
                ['id' => 15187000, 'slug' => 'national-tournament-event'],
            ]],
            [
                'scheduled' => [],
                'hasNextPage' => false,
            ],
        ]);

        $result = $this->endpoint->gamesByDate('Football', '2026-06-28');

        self::assertSame([
            'events' => [
                ['id' => 15186734, 'slug' => 'duplicate-event'],
                ['id' => 15186747, 'slug' => 'world-cup-event'],
                ['id' => 15187000, 'slug' => 'national-tournament-event'],
            ],
        ], $result);
        self::assertSame([
            '/sport/football/scheduled-tournaments/2026-06-28/page/1',
            '/unique-tournament/16/scheduled-events/2026-06-28',
            '/tournament/54661/scheduled-events/2026-06-28',
            '/sport/football/scheduled-tournaments/2026-06-28/page/2',
        ], array_column($this->transport->calls, 'endpoint'));
    }

    public function testGamesByDateDefaultsToToday(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->endpoint->gamesByDate('football');

        self::assertSame("/sport/football/scheduled-tournaments/{$today}/page/1", $this->transport->lastEndpoint());
    }

    public function testGamesByDateRejectsUnknownSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->gamesByDate('quidditch');
    }

    public function testMatchOdds(): void
    {
        $this->endpoint->matchOdds();

        self::assertSame('/event/12345/odds/1/all', $this->transport->lastEndpoint());
    }

    public function testTopTeamStreaks(): void
    {
        $this->endpoint->topTeamStreaks();

        self::assertSame('/odds/top-team-streaks/wins/all', $this->transport->lastEndpoint());
    }

    public function testFeaturedOdds(): void
    {
        $this->endpoint->featuredOdds();

        self::assertSame('/event/12345/odds/1/featured', $this->transport->lastEndpoint());
    }

    public function testH2h(): void
    {
        $this->endpoint->h2h();

        self::assertSame('/event/12345/h2h', $this->transport->lastEndpoint());
    }

    public function testIncidents(): void
    {
        $this->endpoint->incidents();

        self::assertSame('/event/12345/incidents', $this->transport->lastEndpoint());
    }

    public function testBestAwayPlayers(): void
    {
        $this->transport->setResponse(['bestAwayTeamPlayers' => [['id' => 1]]]);
        $result = $this->endpoint->bestAwayPlayers();

        self::assertSame('/event/12345/best-players/summary', $this->transport->lastEndpoint());
        self::assertSame([['id' => 1]], $result);
    }

    public function testBestAwayPlayersDefaultsToNull(): void
    {
        $this->transport->setResponse([]);

        self::assertNull($this->endpoint->bestAwayPlayers());
    }

    public function testBestHomePlayers(): void
    {
        $this->transport->setResponse(['bestHomeTeamPlayers' => [['id' => 2]]]);
        $result = $this->endpoint->bestHomePlayers();

        self::assertSame('/event/12345/best-players/summary', $this->transport->lastEndpoint());
        self::assertSame([['id' => 2]], $result);
    }

    public function testMotm(): void
    {
        $this->transport->setResponse(['playerOfTheMatch' => ['id' => 99]]);
        $result = $this->endpoint->motm();

        self::assertSame('/event/12345/best-players/summary', $this->transport->lastEndpoint());
        self::assertSame(['id' => 99], $result);
    }

    public function testMotmDefaultsToNull(): void
    {
        $this->transport->setResponse([]);

        self::assertNull($this->endpoint->motm());
    }

    public function testVotes(): void
    {
        $this->endpoint->votes();

        self::assertSame('/event/12345/votes', $this->transport->lastEndpoint());
    }

    public function testPreMatchForm(): void
    {
        $this->endpoint->preMatchForm();

        self::assertSame('/event/12345/pregame-form', $this->transport->lastEndpoint());
    }

    public function testMatchChannels(): void
    {
        $this->endpoint->matchChannels();

        self::assertSame('/tv/event/12345/country-channels', $this->transport->lastEndpoint());
    }

    public function testGetChannel(): void
    {
        $this->transport->setResponse([
            'tvChannelVotes' => ['tvChannel' => ['name' => 'Sky Sports']],
        ]);
        $result = $this->endpoint->getChannel(77);

        self::assertSame('/tv/channel/77/event/12345/votes', $this->transport->lastEndpoint());
        self::assertSame('Sky Sports', $result);
    }

    public function testGetChannelDefaultsToNull(): void
    {
        $this->transport->setResponse([]);

        self::assertNull($this->endpoint->getChannel(77));
    }

    public function testChannelSchedule(): void
    {
        $this->endpoint->channelSchedule(77);

        self::assertSame('/tv/channel/77/schedule', $this->transport->lastEndpoint());
    }

    public function testManagers(): void
    {
        $this->endpoint->managers();

        self::assertSame('/event/12345/managers', $this->transport->lastEndpoint());
    }

    public function testLineupsHome(): void
    {
        $this->transport->setResponse([
            'confirmed' => true,
            'home' => [
                'formation' => '4-3-3',
                'playerColor' => ['primary' => '#fff'],
                'goalkeeperColor' => ['primary' => '#000'],
                'missingPlayers' => [['id' => 5]],
                'players' => [
                    ['id' => 1, 'substitute' => false],
                    ['id' => 2, 'substitute' => true],
                ],
            ],
        ]);
        $result = $this->endpoint->lineupsHome();

        self::assertSame('/event/12345/lineups', $this->transport->lastEndpoint());
        self::assertSame([
            'confirmed' => true,
            'formation' => '4-3-3',
            'player_colour' => ['primary' => '#fff'],
            'goalkeeper_colour' => ['primary' => '#000'],
            'missing_players' => [['id' => 5]],
            'starters' => [['id' => 1, 'substitute' => false]],
            'substitutes' => [['id' => 2, 'substitute' => true]],
        ], $result);
    }

    public function testLineupsAway(): void
    {
        $this->transport->setResponse([
            'confirmed' => false,
            'away' => [
                'formation' => '3-5-2',
                'playerColor' => ['primary' => '#abc'],
                'goalkeeperColor' => ['primary' => '#def'],
                'missingPlayers' => [],
                'players' => [
                    ['id' => 10, 'substitute' => true],
                    ['id' => 11, 'substitute' => false],
                ],
            ],
        ]);
        $result = $this->endpoint->lineupsAway();

        self::assertSame('/event/12345/lineups', $this->transport->lastEndpoint());
        self::assertSame([
            'confirmed' => false,
            'formation' => '3-5-2',
            'player_colour' => ['primary' => '#abc'],
            'goalkeeper_colour' => ['primary' => '#def'],
            'missing_players' => [],
            'starters' => [['id' => 11, 'substitute' => false]],
            'substitutes' => [['id' => 10, 'substitute' => true]],
        ], $result);
    }

    public function testShotmapWithoutTeam(): void
    {
        $this->endpoint->shotmap();

        self::assertSame('/event/12345/shotmap', $this->transport->lastEndpoint());
    }

    public function testShotmapWithTeam(): void
    {
        $this->endpoint->shotmap(99);

        self::assertSame('/event/12345/shotmap/99', $this->transport->lastEndpoint());
    }

    public function testHeatmap(): void
    {
        $this->endpoint->heatmap(99);

        self::assertSame('/event/12345/heatmap/99', $this->transport->lastEndpoint());
    }

    public function testStats(): void
    {
        $this->endpoint->stats();

        self::assertSame('/event/12345/statistics', $this->transport->lastEndpoint());
    }

    public function testGetMatchReturnsEvent(): void
    {
        $this->transport->setResponse([
            'event' => [
                'id' => 12345,
                'slug' => 'home-away',
                'startTimestamp' => 1738279800,
            ],
        ]);
        $result = $this->endpoint->getMatch();

        self::assertSame('/event/12345', $this->transport->lastEndpoint());
        self::assertSame(12345, $result->id);
        self::assertSame('home-away', $result->slug);
    }

    public function testHighlight(): void
    {
        $this->endpoint->highlight();

        self::assertSame('/event/12345/highlights', $this->transport->lastEndpoint());
    }

    public function testCommentary(): void
    {
        $this->endpoint->commentary();

        self::assertSame('/event/12345/comments', $this->transport->lastEndpoint());
    }

    public function testTeamStreaks(): void
    {
        $this->endpoint->teamStreaks();

        self::assertSame('/event/12345/team-streaks', $this->transport->lastEndpoint());
    }

    public function testH2hResults(): void
    {
        $this->endpoint->h2hResults('abcXYZ');

        self::assertSame('/event/abcXYZ/h2h/events', $this->transport->lastEndpoint());
    }

    public function testWinProbability(): void
    {
        $this->endpoint->winProbability();

        self::assertSame('/event/12345/graph/win-probability', $this->transport->lastEndpoint());
    }
}
