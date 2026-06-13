<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Endpoint\Team;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Team::class)]
final class TeamTest extends TestCase
{
    private MockTransport $transport;

    private Team $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Team($this->transport, new Enums(), 42);
    }

    public function testTeamReturnsDto(): void
    {
        $this->transport->setResponse(['team' => ['id' => 42, 'name' => 'Arsenal']]);
        $team = $this->endpoint->getTeam();

        self::assertSame('/team/42', $this->transport->lastEndpoint());
        self::assertSame(42, $team->id);
        self::assertSame('Arsenal', $team->name);
        // Round-trip proves Team::fromArray() ran on the unwrapped "team" payload.
        self::assertSame(['id' => 42, 'name' => 'Arsenal'], $team->toArray());
    }

    public function testTeamReturnsDtoWhenPayloadNotWrapped(): void
    {
        $this->transport->setResponse(['id' => 42, 'name' => 'Arsenal']);
        $team = $this->endpoint->getTeam();

        self::assertSame('Arsenal', $team->name);
    }

    public function testGetTeamExposesRawPayloadViaDto(): void
    {
        $this->transport->setResponse(['team' => ['id' => 42, 'extra' => 'kept']]);
        $dto = $this->endpoint->getTeam();

        self::assertSame('/team/42', $this->transport->lastEndpoint());
        self::assertSame(['id' => 42, 'extra' => 'kept'], $dto->toArray());
    }

    public function testImage(): void
    {
        self::assertSame(
            'https://img.sofascore.com/api/v1/team/42/image',
            $this->endpoint->image(),
        );
        self::assertSame(0, $this->transport->callCount());
    }

    public function testPerformance(): void
    {
        $this->endpoint->performance();

        self::assertSame('/team/42/performance', $this->transport->lastEndpoint());
    }

    public function testTransfersInReversesList(): void
    {
        $this->transport->setResponse(['transfersIn' => ['a', 'b', 'c']]);
        $result = $this->endpoint->transfersIn();

        self::assertSame('/team/42/transfers', $this->transport->lastEndpoint());
        self::assertSame(['c', 'b', 'a'], $result);
    }

    public function testTransfersOutReversesList(): void
    {
        $this->transport->setResponse(['transfersOut' => ['x', 'y', 'z']]);
        $result = $this->endpoint->transfersOut();

        self::assertSame('/team/42/transfers', $this->transport->lastEndpoint());
        self::assertSame(['z', 'y', 'x'], $result);
    }

    public function testNextFixturesReversesEvents(): void
    {
        $this->transport->setResponse(['events' => [1, 2, 3]]);
        $result = $this->endpoint->nextFixtures();

        self::assertSame('/team/42/events/next/0', $this->transport->lastEndpoint());
        self::assertSame([3, 2, 1], $result);
    }

    public function testLastFixturesReversesEvents(): void
    {
        $this->transport->setResponse(['events' => [1, 2, 3]]);
        $result = $this->endpoint->lastFixtures();

        self::assertSame('/team/42/events/last/0', $this->transport->lastEndpoint());
        self::assertSame([3, 2, 1], $result);
    }

    public function testSeasons(): void
    {
        $this->endpoint->seasons();

        self::assertSame('/team/42/team-statistics/seasons', $this->transport->lastEndpoint());
    }

    public function testSquad(): void
    {
        $this->endpoint->squad();

        self::assertSame('/team/42/players', $this->transport->lastEndpoint());
    }

    public function testTopPlayers(): void
    {
        $this->endpoint->topPlayers(17, 61627);

        self::assertSame(
            '/team/42/unique-tournament/17/season/61627/top-players/overall',
            $this->transport->lastEndpoint(),
        );
    }

    public function testLeagueStats(): void
    {
        $this->endpoint->leagueStats(17, 61627);

        self::assertSame(
            '/team/42/unique-tournament/17/season/61627/statistics/overall',
            $this->transport->lastEndpoint(),
        );
    }

    public function testLatestHighlights(): void
    {
        $this->endpoint->latestHighlights();

        self::assertSame('/team/42/media', $this->transport->lastEndpoint());
    }

    public function testPerformanceGraph(): void
    {
        $this->endpoint->performanceGraph(17, 61627);

        self::assertSame(
            '/unique-tournament/17/season/61627/team/42/team-performance-graph-data',
            $this->transport->lastEndpoint(),
        );
    }

    public function testNearEvents(): void
    {
        $this->endpoint->nearEvents();

        self::assertSame('/team/42/near-events', $this->transport->lastEndpoint());
    }
}
