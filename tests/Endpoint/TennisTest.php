<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Endpoint\Tennis;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tennis::class)]
final class TennisTest extends TestCase
{
    private MockTransport $transport;

    private Tennis $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Tennis($this->transport, new Enums());
    }

    public function testTotalMatchesExtractsTennisKey(): void
    {
        $this->transport->setResponse([
            'football' => ['live' => 5, 'total' => 100],
            'tennis' => ['live' => 8, 'total' => 16],
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

        self::assertSame('/config/default-unique-tournaments/US/tennis', $this->transport->lastEndpoint());
    }

    public function testCategories(): void
    {
        $this->endpoint->categories();

        self::assertSame('/sport/tennis/categories', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateWithExplicitDateAndSport(): void
    {
        $this->endpoint->matchesByDate('ice-hockey', '2025-01-31');

        self::assertSame('/sport/ice-hockey/scheduled-events/2025-01-31', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateDefaultsSportToCricketAndDateToToday(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->endpoint->matchesByDate();

        self::assertSame("/sport/cricket/scheduled-events/{$today}", $this->transport->lastEndpoint());
    }

    public function testMatchesByDateRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->matchesByDate('quidditch');
    }

    public function testSeasonGames(): void
    {
        $this->endpoint->seasonGames(2363, 57601);

        self::assertSame('/unique-tournament/2363/season/57601/team-events/total', $this->transport->lastEndpoint());
    }

    public function testPowerPerLeg(): void
    {
        $this->endpoint->powerPerLeg(987654);

        self::assertSame('/event/987654/tennis-power', $this->transport->lastEndpoint());
    }

    public function testPointByPoint(): void
    {
        $this->endpoint->pointByPoint(987654);

        self::assertSame('/event/987654/point-by-point', $this->transport->lastEndpoint());
    }

    public function testPlayerTournaments(): void
    {
        $this->endpoint->playerTournaments(123456);

        self::assertSame('/team/123456/recent-unique-tournaments', $this->transport->lastEndpoint());
    }

    public function testPlayerPerformance(): void
    {
        $this->endpoint->playerPerformance(123456);

        self::assertSame('/team/123456/performance', $this->transport->lastEndpoint());
    }

    public function testPlayerNextMatches(): void
    {
        $this->endpoint->playerNextMatches(123456);

        self::assertSame('/team/123456/events/next/0', $this->transport->lastEndpoint());
    }

    public function testPlayerLastMatches(): void
    {
        $this->endpoint->playerLastMatches(123456);

        self::assertSame('/team/123456/events/last/0', $this->transport->lastEndpoint());
    }

    public function testPlayerImage(): void
    {
        self::assertSame(
            'https://img.sofascore.com/api/v1/team/123456/image',
            $this->endpoint->playerImage(123456),
        );
        self::assertSame(0, $this->transport->callCount());
    }
}
