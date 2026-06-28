<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Endpoint\Esports;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Esports::class)]
final class EsportsTest extends TestCase
{
    private MockTransport $transport;

    private Esports $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Esports($this->transport, new Enums());
    }

    public function testTotalMatchesExtractsEsportsKey(): void
    {
        $this->transport->setResponse([
            'football' => ['live' => 5, 'total' => 100],
            'esports' => ['live' => 8, 'total' => 16],
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

    public function testAllTournamentsUppercasesCountryCode(): void
    {
        $this->endpoint->allTournaments('us');

        self::assertSame('/config/default-unique-tournaments/US/esports', $this->transport->lastEndpoint());
    }

    public function testAllTournamentsDefaultsToGb(): void
    {
        $this->endpoint->allTournaments();

        self::assertSame('/config/default-unique-tournaments/GB/esports', $this->transport->lastEndpoint());
    }

    public function testCategories(): void
    {
        $this->endpoint->categories();

        self::assertSame('/sport/esports/categories', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateWithExplicitDateAndSport(): void
    {
        $this->endpoint->matchesByDate('ice-hockey', '2025-01-31');

        self::assertSame('/sport/ice-hockey/scheduled-tournaments/2025-01-31/page/1', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateDefaultsSportToEsportsAndDateToToday(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->endpoint->matchesByDate();

        self::assertSame("/sport/esports/scheduled-tournaments/{$today}/page/1", $this->transport->lastEndpoint());
    }

    public function testMatchesByDateRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->matchesByDate('quidditch');
    }

    public function testTournaments(): void
    {
        $this->endpoint->tournaments(13);

        self::assertSame('/category/13/unique-tournaments', $this->transport->lastEndpoint());
    }

    public function testTournamentSeasons(): void
    {
        $this->endpoint->tournamentSeasons(16026);

        self::assertSame('/unique-tournament/16026/seasons', $this->transport->lastEndpoint());
    }

    public function testTournamentInfo(): void
    {
        $this->endpoint->tournamentInfo(16026, 47832);

        self::assertSame('/unique-tournament/16026/season/47832/info', $this->transport->lastEndpoint());
    }

    public function testGetTournament(): void
    {
        $this->endpoint->getTournament(16026);

        self::assertSame('/unique-tournament/16026', $this->transport->lastEndpoint());
    }

    public function testTournamentMedia(): void
    {
        $this->endpoint->tournamentMedia(16026);

        self::assertSame('/unique-tournament/16026/media', $this->transport->lastEndpoint());
    }

    public function testFeaturedMatches(): void
    {
        $this->endpoint->featuredMatches(16026);

        self::assertSame('/unique-tournament/16026/featured-events', $this->transport->lastEndpoint());
    }

    public function testTournamentCuptree(): void
    {
        $this->endpoint->tournamentCuptree(16026, 47832);

        self::assertSame('/unique-tournament/16026/season/47832/cuptrees', $this->transport->lastEndpoint());
    }

    public function testNextTournamentMatches(): void
    {
        $this->endpoint->nextTournamentMatches(16026, 47832);

        self::assertSame('/unique-tournament/16026/season/47832/events/next/0', $this->transport->lastEndpoint());
    }

    public function testLastTournamentMatches(): void
    {
        $this->endpoint->lastTournamentMatches(16026, 47832);

        self::assertSame('/unique-tournament/16026/season/47832/events/last/0', $this->transport->lastEndpoint());
    }

    public function testTournamentMatches(): void
    {
        $this->endpoint->tournamentMatches(16026, 47832);

        self::assertSame('/unique-tournament/16026/season/47832/events', $this->transport->lastEndpoint());
    }

    public function testGetMatch(): void
    {
        $this->endpoint->getMatch(987654);

        self::assertSame('/event/987654/esports-games', $this->transport->lastEndpoint());
    }

    public function testMatchRounds(): void
    {
        $this->endpoint->matchRounds(987654);

        self::assertSame('/esports-game/987654/rounds', $this->transport->lastEndpoint());
    }

    public function testLineups(): void
    {
        $this->endpoint->lineups(987654);

        self::assertSame('/esports-game/987654/lineups', $this->transport->lastEndpoint());
    }

    public function testTeamStreaks(): void
    {
        $this->endpoint->teamStreaks(987654);

        self::assertSame('/esports-game/987654/team-streaks', $this->transport->lastEndpoint());
    }

    public function testHighlights(): void
    {
        $this->endpoint->highlights(987654);

        self::assertSame('/esports-game/987654/highlights', $this->transport->lastEndpoint());
    }

    public function testLiveMatches(): void
    {
        $this->endpoint->liveMatches();

        self::assertSame('/sport/esports/events/live', $this->transport->lastEndpoint());
    }
}
