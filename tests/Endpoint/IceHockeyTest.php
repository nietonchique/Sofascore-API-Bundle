<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Endpoint\IceHockey;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IceHockey::class)]
final class IceHockeyTest extends TestCase
{
    private MockTransport $transport;

    private IceHockey $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new IceHockey($this->transport, new Enums());
    }

    public function testTotalMatchesExtractsIceHockeyKey(): void
    {
        $this->transport->setResponse([
            'football' => ['live' => 5, 'total' => 100],
            'ice-hockey' => ['live' => 8, 'total' => 16],
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

        self::assertSame('/config/default-unique-tournaments/US/ice-hockey', $this->transport->lastEndpoint());
    }

    public function testAllTournamentsDefaultsToGb(): void
    {
        $this->endpoint->allTournaments();

        self::assertSame('/config/default-unique-tournaments/GB/ice-hockey', $this->transport->lastEndpoint());
    }

    public function testCategories(): void
    {
        $this->endpoint->categories();

        self::assertSame('/sport/ice-hockey/categories', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateWithExplicitDateAndSport(): void
    {
        $this->endpoint->matchesByDate('tennis', '2025-01-31');

        self::assertSame('/sport/tennis/scheduled-tournaments/2025-01-31/page/1', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateDefaultsSportToIceHockeyAndDateToToday(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->endpoint->matchesByDate();

        self::assertSame("/sport/ice-hockey/scheduled-tournaments/{$today}/page/1", $this->transport->lastEndpoint());
    }

    public function testMatchesByDateRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->matchesByDate('quidditch');
    }

    public function testSeasonGames(): void
    {
        $this->endpoint->seasonGames(234, 56789);

        self::assertSame('/unique-tournament/234/season/56789/team-events/total', $this->transport->lastEndpoint());
    }

    public function testTeamTopPlayers(): void
    {
        $this->endpoint->teamTopPlayers(4424, 234, 56789);

        self::assertSame(
            '/team/4424/unique-tournament/234/season/56789/top-players/regularSeason',
            $this->transport->lastEndpoint(),
        );
    }

    public function testPlayerLastYearSummary(): void
    {
        $this->endpoint->playerLastYearSummary(112233);

        self::assertSame('/player/112233/last-year-summary', $this->transport->lastEndpoint());
    }

    public function testPlayerStats(): void
    {
        $this->endpoint->playerStats(112233, 234, 56789);

        self::assertSame(
            '/player/112233/unique-tournament/234/season/56789/statistics/regularSeason',
            $this->transport->lastEndpoint(),
        );
    }

    public function testPlayerShotActions(): void
    {
        $this->endpoint->playerShotActions(112233, 234, 56789);

        self::assertSame(
            '/player/112233/unique-tournament/234/season/56789/shot-actions/regularSeason',
            $this->transport->lastEndpoint(),
        );
    }
}
