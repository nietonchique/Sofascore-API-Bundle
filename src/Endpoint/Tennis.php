<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * Tennis endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class Tennis extends AbstractEndpoint
{
    /**
     * Total count of today's tennis matches and how many are live
     * (Python {@code total_matches}).
     *
     * @return array<string, mixed>
     */
    public function totalMatches(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $tennis */
        $tennis = $data['tennis'] ?? [];

        return $tennis;
    }

    /**
     * All tennis tournaments (Python {@code all_tournaments}).
     *
     * @return array<string, mixed>
     */
    public function allTournaments(string $countryCode): array
    {
        $countryCode = strtoupper($countryCode);

        /** @var array<string, mixed> $data */
        $data = $this->get("/config/default-unique-tournaments/{$countryCode}/tennis");

        return $data;
    }

    /**
     * All tennis categories such as the countries (Python {@code categories}).
     *
     * @return array<string, mixed>
     */
    public function categories(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/tennis/categories');

        return $data;
    }

    /**
     * Scheduled fixtures for a given sport on a specific date
     * (Python {@code matches_by_date}).
     *
     * @return array<string, mixed>
     */
    public function matchesByDate(string $sport = 'cricket', ?string $date = null): array
    {
        return $this->getScheduledEventsByDate($sport, $date);
    }

    /**
     * All matches for the selected tournament and season (Python {@code season_games}).
     *
     * @return array<string, mixed>
     */
    public function seasonGames(int $tournamentId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}/season/{$seasonId}/team-events/total");

        return $data;
    }

    /**
     * Power score per game (round) for the given match (Python {@code power_per_leg}).
     *
     * @return array<string, mixed>
     */
    public function powerPerLeg(int $matchId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$matchId}/tennis-power");

        return $data;
    }

    /**
     * Point by point data for the given match (Python {@code point_by_point}).
     *
     * @return array<string, mixed>
     */
    public function pointByPoint(int $matchId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$matchId}/point-by-point");

        return $data;
    }

    /**
     * The player's recent tournaments (Python {@code player_tournaments}).
     *
     * @return array<string, mixed>
     */
    public function playerTournaments(int $playerId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$playerId}/recent-unique-tournaments");

        return $data;
    }

    /**
     * The player performance (Python {@code player_performance}).
     *
     * @return array<string, mixed>
     */
    public function playerPerformance(int $playerId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$playerId}/performance");

        return $data;
    }

    /**
     * The player's next matches (Python {@code player_next_matches}).
     *
     * @return array<string, mixed>
     */
    public function playerNextMatches(int $playerId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$playerId}/events/next/0");

        return $data;
    }

    /**
     * The player's last matches (Python {@code player_last_matches}).
     *
     * @return array<string, mixed>
     */
    public function playerLastMatches(int $playerId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$playerId}/events/last/0");

        return $data;
    }

    /**
     * URL of the player's image (Python {@code player_image}).
     */
    public function playerImage(int $playerId): string
    {
        return "https://img.sofascore.com/api/v1/team/{$playerId}/image";
    }
}
