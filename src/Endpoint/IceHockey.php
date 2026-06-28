<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * IceHockey endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module (whose class is misnamed `Tennis` in the source).
 */
final class IceHockey extends AbstractEndpoint
{
    /**
     * Total count of today's ice hockey matches and how many are live
     * (Python {@code total_matches}).
     *
     * @return array<string, mixed>
     */
    public function totalMatches(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $iceHockey */
        $iceHockey = $data['ice-hockey'] ?? [];

        return $iceHockey;
    }

    /**
     * All ice hockey tournaments (Python {@code all_tournaments}).
     *
     * @return array<string, mixed>
     */
    public function allTournaments(string $countryCode = 'GB'): array
    {
        $countryCode = strtoupper($countryCode);

        /** @var array<string, mixed> $data */
        $data = $this->get("/config/default-unique-tournaments/{$countryCode}/ice-hockey");

        return $data;
    }

    /**
     * All ice hockey categories such as the countries (Python {@code categories}).
     *
     * @return array<string, mixed>
     */
    public function categories(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/ice-hockey/categories');

        return $data;
    }

    /**
     * Scheduled fixtures for a given sport on a specific date
     * (Python {@code matches_by_date}).
     *
     * @return array<string, mixed>
     */
    public function matchesByDate(string $sport = 'ice-hockey', ?string $date = null): array
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
     * Top players for the given team (Python {@code team_top_players}).
     *
     * @return array<string, mixed>
     */
    public function teamTopPlayers(int $teamId, int $tournamentId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$teamId}/unique-tournament/{$tournamentId}/season/{$seasonId}/top-players/regularSeason");

        return $data;
    }

    /**
     * The player's last year summary (Python {@code player_last_year_summary}).
     *
     * @return array<string, mixed>
     */
    public function playerLastYearSummary(int $playerId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$playerId}/last-year-summary");

        return $data;
    }

    /**
     * A player's statistics for a specific league and season (Python {@code player_stats}).
     *
     * @return array<string, mixed>
     */
    public function playerStats(int $playerId, int $leagueId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$playerId}/unique-tournament/{$leagueId}/season/{$seasonId}/statistics/regularSeason");

        return $data;
    }

    /**
     * A player's shot actions for a specific league and season (Python {@code player_shot_actions}).
     *
     * @return array<string, mixed>
     */
    public function playerShotActions(int $playerId, int $leagueId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$playerId}/unique-tournament/{$leagueId}/season/{$seasonId}/shot-actions/regularSeason");

        return $data;
    }
}
