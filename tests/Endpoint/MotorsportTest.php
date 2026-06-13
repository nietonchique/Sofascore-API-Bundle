<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Endpoint\Motorsport;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Motorsport::class)]
final class MotorsportTest extends TestCase
{
    private MockTransport $transport;

    private Motorsport $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Motorsport($this->transport, new Enums());
    }

    public function testTotalRacesExtractsMotorsportKey(): void
    {
        $this->transport->setResponse(['motorsport' => ['live' => 8, 'total' => 16]]);
        $result = $this->endpoint->totalRaces();

        self::assertSame('/sport/0/event-count', $this->transport->lastEndpoint());
        self::assertSame(['live' => 8, 'total' => 16], $result);
    }

    public function testTotalRacesDefaultsToEmptyArray(): void
    {
        $this->transport->setResponse(['football' => ['live' => 1]]);
        $result = $this->endpoint->totalRaces();

        self::assertSame([], $result);
    }

    public function testCategories(): void
    {
        $this->endpoint->categories();

        self::assertSame('/sport/motorsport/categories', $this->transport->lastEndpoint());
    }

    public function testLiveRaces(): void
    {
        $this->endpoint->liveRaces();

        self::assertSame('/sport/motorsport/events/live', $this->transport->lastEndpoint());
    }

    public function testFeaturedRaces(): void
    {
        $this->endpoint->featuredRaces();

        self::assertSame('/stage/sport/motorsport/featured', $this->transport->lastEndpoint());
    }

    public function testSeasons(): void
    {
        $this->endpoint->seasons(40);

        self::assertSame('/unique-stage/40/seasons', $this->transport->lastEndpoint());
    }

    public function testCurrentSeasonReturnsActiveUniqueStageId(): void
    {
        $now = time();
        $this->transport->setResponse(['seasons' => [
            [
                'startDateTimestamp' => $now - 1000,
                'endDateTimestamp' => $now + 1000,
                'uniqueStage' => ['id' => 40],
            ],
            [
                'startDateTimestamp' => $now - 5000,
                'endDateTimestamp' => $now - 4000,
                'uniqueStage' => ['id' => 99],
            ],
        ]]);

        $result = $this->endpoint->currentSeason(40);

        self::assertSame('/unique-stage/40/seasons', $this->transport->lastEndpoint());
        self::assertSame(40, $result);
    }

    public function testCurrentSeasonReturnsNullWhenNoActiveSeason(): void
    {
        $now = time();
        $this->transport->setResponse(['seasons' => [
            [
                'startDateTimestamp' => $now - 5000,
                'endDateTimestamp' => $now - 4000,
                'uniqueStage' => ['id' => 99],
            ],
        ]]);

        self::assertNull($this->endpoint->currentSeason(40));
    }

    public function testCurrentSeasonReturnsNullWhenNoSeasonsKey(): void
    {
        $this->transport->setResponse([]);

        self::assertNull($this->endpoint->currentSeason(40));
    }

    public function testRaces(): void
    {
        $this->endpoint->races(209766);

        self::assertSame('/stage/209766/substages', $this->transport->lastEndpoint());
    }

    public function testRaceInfo(): void
    {
        $this->endpoint->raceInfo(209767);

        self::assertSame('/stage/209767', $this->transport->lastEndpoint());
    }

    public function testDriverRankings(): void
    {
        $this->endpoint->driverRankings(206455);

        self::assertSame('/stage/206455/standings/competitor', $this->transport->lastEndpoint());
    }

    public function testTeamRankings(): void
    {
        $this->endpoint->teamRankings(206455);

        self::assertSame('/stage/206455/standings/team', $this->transport->lastEndpoint());
    }

    public function testRaceResults(): void
    {
        $this->endpoint->raceResults(209767);

        self::assertSame('/stage/209767/standings/competitor', $this->transport->lastEndpoint());
    }

    public function testRaceImage(): void
    {
        self::assertSame(
            'https://img.sofascore.com/api/v1/stage/209767/image',
            $this->endpoint->raceImage(209767),
        );
        self::assertSame(0, $this->transport->callCount());
    }

    public function testTeamImage(): void
    {
        self::assertSame(
            'https://img.sofascore.com/api/v1/team/214902/image',
            $this->endpoint->teamImage(214902),
        );
        self::assertSame(0, $this->transport->callCount());
    }

    public function testDriverImage(): void
    {
        self::assertSame(
            'https://img.sofascore.com/api/v1/team/191417/image',
            $this->endpoint->driverImage(191417),
        );
        self::assertSame(0, $this->transport->callCount());
    }

    public function testDriverInfo(): void
    {
        $this->endpoint->driverInfo(191417);

        self::assertSame('/team/191417', $this->transport->lastEndpoint());
    }

    public function testTeamInfo(): void
    {
        $this->endpoint->teamInfo(214902);

        self::assertSame('/team/214902', $this->transport->lastEndpoint());
    }

    public function testDriverSeasons(): void
    {
        $this->endpoint->driverSeasons(191417);

        self::assertSame('/team/191417/stage-seasons', $this->transport->lastEndpoint());
    }

    public function testTeamSeasons(): void
    {
        $this->endpoint->teamSeasons(214902);

        self::assertSame('/team/214902/stage-seasons', $this->transport->lastEndpoint());
    }

    public function testDriverRaces(): void
    {
        $this->endpoint->driverRaces(191417, 206455);

        self::assertSame('/team/191417/stage-season/206455/races', $this->transport->lastEndpoint());
    }

    public function testTeamRaces(): void
    {
        $this->endpoint->teamRaces(214902, 206455);

        self::assertSame('/team/214902/stage-season/206455/races', $this->transport->lastEndpoint());
    }
}
