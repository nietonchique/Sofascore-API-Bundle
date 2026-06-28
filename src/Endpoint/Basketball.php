<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * Basketball endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class Basketball extends AbstractEndpoint
{
    /**
     * Total count of today's basketball games and how many are live
     * (Python {@code total_games}).
     *
     * @return array<string, mixed>
     */
    public function totalGames(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $basketball */
        $basketball = $data['basketball'] ?? [];

        return $basketball;
    }

    /**
     * All currently live basketball games (Python {@code live_games}).
     *
     * @return array<string, mixed>
     */
    public function liveGames(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/basketball/events/live');

        return $data;
    }

    /**
     * Fixtures for today or a specific date (Python {@code games_by_date}).
     *
     * @return array<string, mixed>
     */
    public function gamesByDate(string $sport = 'basketball', ?string $date = null): array
    {
        return $this->getScheduledEventsByDate($sport, $date);
    }

    /**
     * A player's ratings for a specific league and season
     * (Python {@code player_ratings}).
     *
     * @return array<string, mixed>
     */
    public function playerRatings(int $playerId, int $leagueId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$playerId}/unique-tournament/{$leagueId}/season/{$seasonId}/ratings");

        return $data;
    }

    /**
     * A player's seasons (Python {@code player_seasons}).
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
     * A player's statistics for a specific league (Python {@code player_stats}).
     *
     * @return array<string, mixed>
     */
    public function playerStats(int $playerId, int $leagueId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/player/{$playerId}/unique-tournament/{$leagueId}/statistics/regularSeason");

        return $data;
    }

    /**
     * Top players' statistics per game for a league and season
     * (Python {@code top_players_per_game}).
     *
     * @return array<string, mixed>
     */
    public function topPlayersPerGame(int $leagueId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$leagueId}/season/{$seasonId}/top-players-per-game/all/regularSeason");

        return $data;
    }

    /**
     * Top players' statistics per season for a league and season
     * (Python {@code top_players_per_season}).
     *
     * @return array<string, mixed>
     */
    public function topPlayersPerSeason(int $leagueId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$leagueId}/season/{$seasonId}/top-players/regularSeason");

        return $data;
    }

    /**
     * Top teams' statistics per season for a league and season
     * (Python {@code top_teams_per_season}).
     *
     * @return array<string, mixed>
     */
    public function topTeamsPerSeason(int $leagueId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$leagueId}/season/{$seasonId}/top-teams/regularSeason");

        return $data;
    }
}
