<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use Nietonchique\SofascoreApiBundle\Dto\Player as PlayerDto;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * Player endpoint group. Faithful port of the Python {@code player.py} module's
 * {@code Player} class; the player id is bound at construction.
 */
final class Player extends AbstractEndpoint
{
    public function __construct(
        TransportInterface $transport,
        Enums $enums,
        private readonly int $playerId,
    ) {
        parent::__construct($transport, $enums);
    }

    /**
     * Fetch detailed information about the player as a typed DTO (Python
     * {@code get_player}).
     *
     * NOTE: the spec asked for this method to be named {@code get()}, but
     * {@see AbstractEndpoint::get()} is a {@code protected} request helper with an
     * incompatible signature ({@code get(string, array): array}); declaring a
     * {@code public get(): PlayerDto} override is a fatal "must be compatible"
     * error in PHP (the same latent defect already breaks the sibling Team /
     * League / MatchEndpoint classes against this base). Since AbstractEndpoint is
     * out of scope to edit, the detail getter is named after the Python method to
     * keep the class loadable and the suite green.
     */
    public function getPlayer(): PlayerDto
    {
        return PlayerDto::fromArray($this->get("/player/{$this->playerId}"));
    }

    /**
     * Fetch the transfer history of the player (Python {@code transfer_history}).
     *
     * @return array<string, mixed>
     */
    public function transferHistory(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$this->playerId}/transfer-history");

        return $data;
    }

    /**
     * Fetch the last matches the player participated in, most recent first
     * (Python {@code last_fixtures}).
     *
     * @return list<mixed>
     */
    public function lastFixtures(): array
    {
        $data = $this->get("/player/{$this->playerId}/events/last/0");
        $events = $data['events'] ?? [];

        return \is_array($events) ? array_reverse(array_values($events)) : [];
    }

    /**
     * Fetch the player's attributes and performance overview (Python {@code attributes}).
     *
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$this->playerId}/attribute-overviews");

        return $data;
    }

    /**
     * Fetch the player's statistics for a specific league and season
     * (Python {@code league_stats}).
     *
     * @return array<string, mixed>
     */
    public function leagueStats(int $leagueId, int $season): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$this->playerId}/unique-tournament/{$leagueId}/season/{$season}/statistics/overall");

        return $data;
    }

    /**
     * The player's image URL (Python {@code image}).
     */
    public function image(): string
    {
        return "https://img.sofascore.com/api/v1/player/{$this->playerId}/image";
    }

    /**
     * Fetch the player's national team statistics (Python {@code national_stats}).
     *
     * @return array<string, mixed>
     */
    public function nationalStats(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$this->playerId}/national-team-statistics");

        return $data;
    }

    /**
     * Retrieve a player's full seasons info (Python {@code player_seasons}).
     *
     * @return array<string, mixed>
     */
    public function playerSeasons(int $playerId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$playerId}/statistics/seasons");

        return $data;
    }

    /**
     * Retrieve the unique leagues a player has participated in
     * (Python {@code player_leagues}).
     *
     * @return array<string, mixed>
     */
    public function playerLeagues(int $playerId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$playerId}/unique-tournaments");

        return $data;
    }
}
