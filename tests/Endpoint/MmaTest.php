<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Endpoint\Mma;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Mma::class)]
final class MmaTest extends TestCase
{
    private MockTransport $transport;

    private Mma $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Mma($this->transport, new Enums());
    }

    public function testTotalFightsUnwrapsMmaKey(): void
    {
        $this->transport->setResponse(['mma' => ['live' => 21, 'total' => 270], 'football' => []]);
        $result = $this->endpoint->totalFights();

        self::assertSame('/sport/0/event-count', $this->transport->lastEndpoint());
        self::assertSame(['live' => 21, 'total' => 270], $result);
    }

    public function testTotalFightsDefaultsToEmptyArray(): void
    {
        $this->transport->setResponse(['football' => ['live' => 1]]);
        $result = $this->endpoint->totalFights();

        self::assertSame([], $result);
    }

    public function testLiveFights(): void
    {
        $this->endpoint->liveFights();

        self::assertSame('/sport/mma/events/live', $this->transport->lastEndpoint());
    }

    public function testFightsByDateWithDefaults(): void
    {
        $this->endpoint->fightsByDate();

        $expectedDate = (new DateTimeImmutable())->format('Y-m-d');
        self::assertSame("/sport/mma/scheduled-tournaments/{$expectedDate}/page/1", $this->transport->lastEndpoint());
    }

    public function testFightsByDateWithSportAndDate(): void
    {
        $this->endpoint->fightsByDate('football', '2025-02-06');

        self::assertSame('/sport/football/scheduled-tournaments/2025-02-06/page/1', $this->transport->lastEndpoint());
    }

    public function testFightsByDateRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->endpoint->fightsByDate('quidditch');
    }

    public function testFighterCareerStats(): void
    {
        $this->endpoint->fighterCareerStats(12345);

        self::assertSame('/team/12345/career-statistics', $this->transport->lastEndpoint());
    }

    public function testFighterNextFightsReversesEvents(): void
    {
        $this->transport->setResponse(['events' => [1, 2, 3]]);
        $result = $this->endpoint->fighterNextFights(12345);

        self::assertSame('/team/12345/events/next/0', $this->transport->lastEndpoint());
        self::assertSame([3, 2, 1], $result);
    }

    public function testFighterLastFightsReversesEvents(): void
    {
        $this->transport->setResponse(['events' => [1, 2, 3]]);
        $result = $this->endpoint->fighterLastFights(12345);

        self::assertSame('/team/12345/events/last/0', $this->transport->lastEndpoint());
        self::assertSame([3, 2, 1], $result);
    }

    public function testFighterRankings(): void
    {
        $this->endpoint->fighterRankings(12345);

        self::assertSame('/rankings/team/12345', $this->transport->lastEndpoint());
    }

    public function testFighterInfo(): void
    {
        $this->endpoint->fighterInfo(12345);

        self::assertSame('/team/12345', $this->transport->lastEndpoint());
    }

    public function testMainEventsDateWithDefault(): void
    {
        $this->endpoint->mainEventsDate();

        $expectedDate = (new DateTimeImmutable())->format('Y-m-d');
        self::assertSame("/sport/mma/main-events/{$expectedDate}/extended", $this->transport->lastEndpoint());
    }

    public function testMainEventsDateWithDate(): void
    {
        $this->endpoint->mainEventsDate('2025-02-06');

        self::assertSame('/sport/mma/main-events/2025-02-06/extended', $this->transport->lastEndpoint());
    }

    public function testMainEventsMonthWithDefault(): void
    {
        $this->endpoint->mainEventsMonth(19906);

        $expectedDate = (new DateTimeImmutable())->format('Y-m');
        self::assertSame(
            "/unique-tournament/19906/scheduled-mma-main-events/{$expectedDate}",
            $this->transport->lastEndpoint(),
        );
    }

    public function testMainEventsMonthWithDate(): void
    {
        $this->endpoint->mainEventsMonth(19906, '2025-02');

        self::assertSame(
            '/unique-tournament/19906/scheduled-mma-main-events/2025-02',
            $this->transport->lastEndpoint(),
        );
    }

    public function testMmaTournaments(): void
    {
        $this->endpoint->mmaTournaments();

        self::assertSame('/category/1708/unique-tournaments', $this->transport->lastEndpoint());
    }

    public function testMmaTournamentsMonths(): void
    {
        $this->endpoint->mmaTournamentsMonths(19906);

        self::assertSame(
            '/calendar/unique-tournament/19906/0/months-with-events',
            $this->transport->lastEndpoint(),
        );
    }

    public function testTournamentInfo(): void
    {
        $this->endpoint->tournamentInfo(19906);

        self::assertSame('/unique-tournament/19906', $this->transport->lastEndpoint());
    }

    public function testFighterImage(): void
    {
        self::assertSame(
            'https://img.sofascore.com/api/v1/team/12345/image',
            $this->endpoint->fighterImage(12345),
        );
        self::assertSame(0, $this->transport->callCount());
    }

    public function testTournamentImage(): void
    {
        self::assertSame(
            'https://img.sofascore.com/api/v1/unique-tournament/19906/image/dark',
            $this->endpoint->tournamentImage(19906),
        );
        self::assertSame(0, $this->transport->callCount());
    }

    public function testRankingSummary(): void
    {
        $this->endpoint->rankingSummary(19906);

        self::assertSame('/unique-tournament/19906/summary', $this->transport->lastEndpoint());
    }

    public function testRankings(): void
    {
        $this->endpoint->rankings(555);

        self::assertSame('/rankings/555', $this->transport->lastEndpoint());
    }
}
