<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Endpoint\Baseball;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Baseball::class)]
final class BaseballTest extends TestCase
{
    private MockTransport $transport;

    private Baseball $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Baseball($this->transport, new Enums());
    }

    public function testTotalMatchesExtractsBaseballKey(): void
    {
        $this->transport->setResponse([
            'football' => ['live' => 5, 'total' => 100],
            'baseball' => ['live' => 8, 'total' => 16],
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

    public function testAllTournamentsDefaultsCountry(): void
    {
        $this->endpoint->allTournaments();

        self::assertSame('/config/default-unique-tournaments/GB/baseball', $this->transport->lastEndpoint());
    }

    public function testAllTournamentsUppercasesCountry(): void
    {
        $this->endpoint->allTournaments('us');

        self::assertSame('/config/default-unique-tournaments/US/baseball', $this->transport->lastEndpoint());
    }

    public function testCategories(): void
    {
        $this->endpoint->categories();

        self::assertSame('/sport/baseball/categories', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateWithExplicitDateAndSport(): void
    {
        $this->endpoint->matchesByDate('ice-hockey', '2025-01-31');

        self::assertSame('/sport/ice-hockey/scheduled-events/2025-01-31', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateDefaultsSportAndDate(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->endpoint->matchesByDate();

        self::assertSame("/sport/baseball/scheduled-events/{$today}", $this->transport->lastEndpoint());
    }

    public function testMatchesByDateNormalisesSportSlug(): void
    {
        $this->endpoint->matchesByDate('Ice Hockey', '2025-01-31');

        self::assertSame('/sport/ice-hockey/scheduled-events/2025-01-31', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->matchesByDate('quidditch');
    }

    public function testSeasonGames(): void
    {
        $this->endpoint->seasonGames(132, 65360);

        self::assertSame('/unique-tournament/132/season/65360/team-events/total', $this->transport->lastEndpoint());
    }

    public function testPlayerLastYearSummary(): void
    {
        $this->endpoint->playerLastYearSummary(7);

        self::assertSame('/player/7/last-year-summary', $this->transport->lastEndpoint());
    }

    public function testPlayerStats(): void
    {
        $this->endpoint->playerStats(7, 132, 65360);

        self::assertSame('/player/7/unique-tournament/132/season/65360/statistics/regularSeason', $this->transport->lastEndpoint());
    }

    public function testTeamSeasons(): void
    {
        $this->endpoint->teamSeasons(42);

        self::assertSame('/team/42/standings/seasons', $this->transport->lastEndpoint());
    }
}
