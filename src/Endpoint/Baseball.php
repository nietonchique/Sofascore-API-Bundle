<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * Baseball endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class Baseball extends AbstractEndpoint
{
    /**
     * Total count of today's baseball matches and how many are live (Python {@code total_matches}).
     *
     * @return array<string, mixed>
     */
    public function totalMatches(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $baseball */
        $baseball = $data['baseball'] ?? [];

        return $baseball;
    }

    /**
     * All the baseball tournaments (Python {@code all_tournaments}).
     *
     * @return array<string, mixed>
     */
    public function allTournaments(string $countryCode = 'GB'): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/config/default-unique-tournaments/'.strtoupper($countryCode).'/baseball');

        return $data;
    }

    /**
     * All the baseball categories such as the countries (Python {@code categories}).
     *
     * @return array<string, mixed>
     */
    public function categories(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/baseball/categories');

        return $data;
    }

    /**
     * Scheduled fixtures for a given sport on a specific date (Python {@code matches_by_date}).
     *
     * @return array<string, mixed>
     */
    public function matchesByDate(string $sport = 'baseball', ?string $date = null): array
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
     * A team's seasons (Python {@code team_seasons}).
     *
     * @return array<string, mixed>
     */
    public function teamSeasons(int $teamId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$teamId}/standings/seasons");

        return $data;
    }
}
