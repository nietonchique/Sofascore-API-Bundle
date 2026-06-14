<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Dto;

use Nietonchique\SofascoreApiBundle\Dto\FieldTranslations;
use Nietonchique\SofascoreApiBundle\Dto\LanguageCode;
use Nietonchique\SofascoreApiBundle\Endpoint\League;
use Nietonchique\SofascoreApiBundle\Endpoint\MatchEndpoint;
use Nietonchique\SofascoreApiBundle\Endpoint\Player;
use Nietonchique\SofascoreApiBundle\Endpoint\Team;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\TestCase;

/**
 * Validates the entity DTOs against **real, frozen** SofaScore API responses
 * (captured in tests/fixtures/dto/). Exercises the full chain — endpoint
 * unwrapping + DTO mapping — so type mismatches, wrong keys and missing
 * unwrapping are caught.
 */
final class DtoRealDataTest extends TestCase
{
    /**
     * @return array<array-key, mixed>
     */
    private function fixture(string $name): array
    {
        $json = file_get_contents(__DIR__.'/../fixtures/dto/'.$name.'.json');
        self::assertIsString($json);
        $data = json_decode($json, true);
        self::assertIsArray($data);

        return $data;
    }

    private function endpoint(string $fixture): MockTransport
    {
        return new MockTransport($this->fixture($fixture));
    }

    public function testTeamFromRealResponse(): void
    {
        $team = (new Team($this->endpoint('team'), new Enums(), 42))->getTeam();

        self::assertSame(42, $team->id);
        self::assertSame('Arsenal', $team->name);
        self::assertSame('Arsenal', $team->fullName);
        self::assertSame('M', $team->gender);
        self::assertFalse($team->national);
        self::assertSame('football', $team->sport?->slug);
        self::assertSame('EN', $team->country?->alpha2);
        self::assertSame('#cc0000', $team->teamColors?->primary);
        self::assertSame('England', $team->category?->name);
        self::assertInstanceOf(FieldTranslations::class, $team->fieldTranslations);
        self::assertSame('Арсенал', $team->fieldTranslations->nameIn(LanguageCode::RU));
    }

    public function testPlayerFromRealResponse(): void
    {
        $player = (new Player($this->endpoint('player'), new Enums(), 12994))->getPlayer();

        self::assertSame(12994, $player->id);
        self::assertSame('Lionel Messi', $player->name);
        // jerseyNumber is a string in the API; shirtNumber is the int.
        self::assertSame('10', $player->jerseyNumber);
        self::assertIsString($player->jerseyNumber);
        self::assertSame(10, $player->shirtNumber);
        self::assertSame(169, $player->height);
        self::assertIsInt($player->dateOfBirthTimestamp);
        self::assertSame('AR', $player->country?->alpha2);
        self::assertSame('Inter Miami CF', $player->team?->name);
        self::assertInstanceOf(FieldTranslations::class, $player->fieldTranslations);
        self::assertSame('Лионель Месси', $player->fieldTranslations->nameIn(LanguageCode::RU));
        self::assertSame('Л. Месси', $player->fieldTranslations->shortNameIn(LanguageCode::RU));
    }

    public function testTournamentFromRealResponse(): void
    {
        $tournament = (new League($this->endpoint('tournament'), new Enums(), 17))->getLeague();

        self::assertSame(17, $tournament->id);
        self::assertSame('Premier League', $tournament->name);
        self::assertSame('premier-league', $tournament->slug);
        // The top-level response has no "sport" — it must be resolved from category.sport.
        self::assertSame('football', $tournament->sport?->slug);
        self::assertSame('England', $tournament->category?->name);
        self::assertSame('#3c1c5a', $tournament->primaryColorHex);
        self::assertInstanceOf(FieldTranslations::class, $tournament->fieldTranslations);
        self::assertSame('الدوري الإنجليزي الممتاز', $tournament->fieldTranslations->nameIn(LanguageCode::AR));
        self::assertInstanceOf(FieldTranslations::class, $tournament->category->fieldTranslations);
        self::assertSame('Англия', $tournament->category->fieldTranslations->nameIn(LanguageCode::RU));
    }

    public function testEventFromRealResponse(): void
    {
        $event = (new MatchEndpoint($this->endpoint('event'), new Enums(), 14566612))->getMatch();

        self::assertSame(14566612, $event->id);
        self::assertSame('inter-arsenal', $event->slug);
        self::assertSame('finished', $event->statusType);
        self::assertSame(100, $event->statusCode);
        self::assertSame('Ended', $event->statusDescription);
        self::assertSame('Inter', $event->homeTeam?->name);
        self::assertNotNull($event->awayTeam);
        // finished match → populated scores.
        self::assertSame(1, $event->homeScore?->current);
        self::assertNotNull($event->awayScore);
        self::assertInstanceOf(FieldTranslations::class, $event->homeTeam->fieldTranslations);
        self::assertSame('Интер', $event->homeTeam->fieldTranslations->nameIn(LanguageCode::RU));
    }
}
