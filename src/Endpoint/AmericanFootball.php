<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * AmericanFootball endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class AmericanFootball extends AbstractEndpoint
{
    /**
     * Total count of today's american football matches and how many are live
     * (Python {@code total_matches}).
     *
     * @return array<string, mixed>
     */
    public function totalMatches(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $americanFootball */
        $americanFootball = $data['american-football'] ?? [];

        return $americanFootball;
    }

    /**
     * Scheduled fixtures for a given sport on a specific date
     * (Python {@code matches_by_date}).
     *
     * @return array<string, mixed>
     */
    public function matchesByDate(string $sport = 'american-football', ?string $date = null): array
    {
        return $this->getScheduledEventsByDate($sport, $date);
    }

    /**
     * All american football categories such as NFL Europa and USA
     * (Python {@code categories}).
     *
     * @return array<string, mixed>
     */
    public function categories(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/american-football/categories');

        return $data;
    }

    /**
     * All tournaments for a selected category (Python {@code tournaments}).
     *
     * @return array<string, mixed>
     */
    public function tournaments(int $categoryId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/category/{$categoryId}/unique-tournaments");

        return $data;
    }

    /**
     * Best players for the selected team, tournament and season's playoffs
     * (Python {@code best_player_of_playoffs}).
     *
     * @return array<string, mixed>
     */
    public function bestPlayerOfPlayoffs(int $teamId, int $tournamentId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$teamId}/unique-tournament/{$tournamentId}/season/{$seasonId}/top-players/playoffs");

        return $data;
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
     * Tournament info for a season (Python {@code tournament_info}).
     *
     * @return array<string, mixed>
     */
    public function tournamentInfo(int $tournamentId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}/season/{$seasonId}/info");

        return $data;
    }

    /**
     * Highlights for the given round (Python {@code round_highlights}).
     *
     * @return array<string, mixed>
     */
    public function roundHighlights(string $countryCode, int $tournamentId, int $seasonId, int $round): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/sport-video-highlights/country/{$countryCode}/unique-tournament/{$tournamentId}/season/{$seasonId}/round/{$round}");

        return $data;
    }

    /**
     * Seasons for a given american football team (Python {@code team_seasons}).
     *
     * @return array<string, mixed>
     */
    public function teamSeasons(int $teamId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$teamId}/standings/seasons");

        return $data;
    }

    /**
     * Current standings for the tournament and season (Python {@code standings}).
     *
     * @return array<string, mixed>
     */
    public function standings(int $tournamentId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/tournament/{$tournamentId}/season/{$seasonId}/standings/total");

        return $data;
    }

    /**
     * Nearest games for the selected team (Python {@code team_near_games}).
     *
     * @return array<string, mixed>
     */
    public function teamNearGames(int $teamId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$teamId}/near-events");

        return $data;
    }

    /**
     * A team's best players for a given season (Python {@code team_player_stats}).
     *
     * @return array<string, mixed>
     */
    public function teamPlayerStats(int $teamId, int $leagueId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$teamId}/unique-tournament/{$leagueId}/season/{$seasonId}/player-statistics/regularSeason");

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
}
