<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Dto;

use Nietonchique\SofascoreApiBundle\Dto\Country;
use Nietonchique\SofascoreApiBundle\Dto\Event;
use Nietonchique\SofascoreApiBundle\Dto\Player;
use Nietonchique\SofascoreApiBundle\Dto\Score;
use Nietonchique\SofascoreApiBundle\Dto\Sport;
use Nietonchique\SofascoreApiBundle\Dto\Team;
use Nietonchique\SofascoreApiBundle\Dto\Tournament;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Event::class)]
#[CoversClass(Team::class)]
#[CoversClass(Player::class)]
#[CoversClass(Tournament::class)]
#[CoversClass(Sport::class)]
#[CoversClass(Country::class)]
#[CoversClass(Score::class)]
final class DtoTest extends TestCase
{
    public function testTeamParsesNestedSportAndCountry(): void
    {
        $team = Team::fromArray([
            'id' => 42,
            'name' => 'Arsenal',
            'shortName' => 'Arsenal',
            'slug' => 'arsenal',
            'nameCode' => 'ARS',
            'national' => false,
            'userCount' => 3712685,
            'sport' => ['id' => 1, 'slug' => 'football', 'name' => 'Football'],
            'country' => ['alpha2' => 'EN', 'name' => 'England'],
        ]);

        self::assertSame(42, $team->id);
        self::assertSame('Arsenal', $team->name);
        self::assertFalse($team->national);
        self::assertSame('football', $team->sport?->slug);
        self::assertSame('EN', $team->country?->alpha2);
    }

    public function testTeamToArrayReturnsRaw(): void
    {
        $raw = ['id' => 42, 'name' => 'Arsenal', 'extra' => ['nested' => true]];
        self::assertSame($raw, Team::fromArray($raw)->toArray());
    }

    public function testPlayerParsesTeamAndCountry(): void
    {
        $player = Player::fromArray([
            'id' => 7,
            'name' => 'Bukayo Saka',
            'position' => 'F',
            'jerseyNumber' => 7,
            'country' => ['alpha2' => 'EN'],
            'team' => ['id' => 42, 'name' => 'Arsenal'],
        ]);

        self::assertSame(7, $player->id);
        self::assertSame('F', $player->position);
        self::assertSame(7, $player->jerseyNumber);
        self::assertSame('Arsenal', $player->team?->name);
        self::assertSame('EN', $player->country?->alpha2);
    }

    public function testTournamentReadsCategoryAsCountry(): void
    {
        $t = Tournament::fromArray([
            'id' => 17,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'sport' => ['id' => 1, 'slug' => 'football'],
            'category' => ['name' => 'England', 'alpha2' => 'EN'],
        ]);

        self::assertSame(17, $t->id);
        self::assertSame('football', $t->sport?->slug);
        self::assertSame('England', $t->category?->name);
    }

    public function testEventParsesTeamsAndScores(): void
    {
        $event = Event::fromArray([
            'id' => 12345,
            'slug' => 'arsenal-chelsea',
            'startTimestamp' => 1700000000,
            'status' => ['type' => 'finished'],
            'winnerCode' => 1,
            'homeTeam' => ['id' => 42, 'name' => 'Arsenal'],
            'awayTeam' => ['id' => 38, 'name' => 'Chelsea'],
            'homeScore' => ['current' => 2, 'period1' => 1],
            'awayScore' => ['current' => 1],
        ]);

        self::assertSame(12345, $event->id);
        self::assertSame('finished', $event->statusType);
        self::assertSame(1, $event->winnerCode);
        self::assertSame('Arsenal', $event->homeTeam?->name);
        self::assertSame(2, $event->homeScore?->current);
        self::assertSame(1, $event->awayScore?->current);
    }

    public function testEventUnwrapsEventKey(): void
    {
        $event = Event::fromArray(['event' => ['id' => 99, 'slug' => 'x']]);

        self::assertSame(99, $event->id);
        self::assertSame('x', $event->slug);
    }

    public function testToleratesMissingOptionalFields(): void
    {
        $event = Event::fromArray(['id' => 1]);

        self::assertSame(1, $event->id);
        self::assertNull($event->homeTeam);
        self::assertNull($event->homeScore);
        self::assertNull($event->tournament);

        $score = Score::fromArray([]);
        self::assertNull($score->current);
    }
}
