<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Endpoint\Player;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Player::class)]
final class PlayerTest extends TestCase
{
    private function endpoint(MockTransport $transport): Player
    {
        return new Player($transport, new Enums(), 776);
    }

    public function testGetReturnsPlayerDto(): void
    {
        $transport = new MockTransport(['id' => 776, 'name' => 'Bukayo Saka', 'slug' => 'bukayo-saka']);
        $player = $this->endpoint($transport)->getPlayer();

        self::assertSame(776, $player->id);
        self::assertSame('Bukayo Saka', $player->name);
        self::assertSame('/player/776', $transport->lastEndpoint());
    }

    public function testTransferHistory(): void
    {
        $transport = new MockTransport(['transferHistory' => []]);
        $result = $this->endpoint($transport)->transferHistory();

        self::assertSame(['transferHistory' => []], $result);
        self::assertSame('/player/776/transfer-history', $transport->lastEndpoint());
    }

    public function testLastFixturesReversesEvents(): void
    {
        $transport = new MockTransport(['events' => [['id' => 1], ['id' => 2], ['id' => 3]]]);
        $result = $this->endpoint($transport)->lastFixtures();

        self::assertSame([['id' => 3], ['id' => 2], ['id' => 1]], $result);
        self::assertSame('/player/776/events/last/0', $transport->lastEndpoint());
    }

    public function testLastFixturesMissingEventsKey(): void
    {
        $transport = new MockTransport([]);
        $result = $this->endpoint($transport)->lastFixtures();

        self::assertSame([], $result);
    }

    public function testAttributes(): void
    {
        $transport = new MockTransport(['averageAttributeOverviews' => []]);
        $result = $this->endpoint($transport)->attributes();

        self::assertSame(['averageAttributeOverviews' => []], $result);
        self::assertSame('/player/776/attribute-overviews', $transport->lastEndpoint());
    }

    public function testLeagueStats(): void
    {
        $transport = new MockTransport(['statistics' => ['rating' => 6.9]]);
        $result = $this->endpoint($transport)->leagueStats(17, 56953);

        self::assertSame(['statistics' => ['rating' => 6.9]], $result);
        self::assertSame('/player/776/unique-tournament/17/season/56953/statistics/overall', $transport->lastEndpoint());
    }

    public function testImageBuildsUrlWithoutRequest(): void
    {
        $transport = new MockTransport();
        $url = $this->endpoint($transport)->image();

        self::assertSame('https://img.sofascore.com/api/v1/player/776/image', $url);
        self::assertSame(0, $transport->callCount());
    }

    public function testNationalStats(): void
    {
        $transport = new MockTransport(['statistics' => []]);
        $result = $this->endpoint($transport)->nationalStats();

        self::assertSame(['statistics' => []], $result);
        self::assertSame('/player/776/national-team-statistics', $transport->lastEndpoint());
    }

    public function testPlayerSeasons(): void
    {
        $transport = new MockTransport(['uniqueTournamentSeasons' => []]);
        $result = $this->endpoint($transport)->playerSeasons(934235);

        self::assertSame(['uniqueTournamentSeasons' => []], $result);
        self::assertSame('/player/934235/statistics/seasons', $transport->lastEndpoint());
    }

    public function testPlayerLeagues(): void
    {
        $transport = new MockTransport(['uniqueTournaments' => []]);
        $result = $this->endpoint($transport)->playerLeagues(934235);

        self::assertSame(['uniqueTournaments' => []], $result);
        self::assertSame('/player/934235/unique-tournaments', $transport->lastEndpoint());
    }
}
