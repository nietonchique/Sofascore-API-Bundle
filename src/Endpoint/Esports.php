<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * Esports endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class Esports extends AbstractEndpoint
{
    /**
     * Total count of today's e-sport matches and how many are live
     * (Python {@code total_matches}).
     *
     * @return array<string, mixed>
     */
    public function totalMatches(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $esports */
        $esports = $data['esports'] ?? [];

        return $esports;
    }

    /**
     * All e-sport tournaments (Python {@code all_tournaments}).
     *
     * @return array<string, mixed>
     */
    public function allTournaments(string $countryCode = 'GB'): array
    {
        $countryCode = strtoupper($countryCode);

        /** @var array<string, mixed> $data */
        $data = $this->get("/config/default-unique-tournaments/{$countryCode}/esports");

        return $data;
    }

    /**
     * All esports categories such as LoL, Counter-Strike (Python {@code categories}).
     *
     * @return array<string, mixed>
     */
    public function categories(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/esports/categories');

        return $data;
    }

    /**
     * Scheduled fixtures for a given sport on a specific date
     * (Python {@code matches_by_date}).
     *
     * @return array<string, mixed>
     */
    public function matchesByDate(string $sport = 'esports', ?string $date = null): array
    {
        return $this->getScheduledEventsByDate($sport, $date);
    }

    /**
     * All tournaments for a selected esports category (Python {@code tournaments}).
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
     * Seasons of a tournament (Python {@code tournament_seasons}).
     *
     * @return array<string, mixed>
     */
    public function tournamentSeasons(int $tournamentId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}/seasons");

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
     * The tournament (Python {@code get_tournament}).
     *
     * @return array<string, mixed>
     */
    public function getTournament(int $tournamentId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}");

        return $data;
    }

    /**
     * Tournament media such as highlights and streams (Python {@code tournament_media}).
     *
     * @return array<string, mixed>
     */
    public function tournamentMedia(int $tournamentId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}/media");

        return $data;
    }

    /**
     * Tournament's current featured matches (Python {@code featured_matches}).
     *
     * @return array<string, mixed>
     */
    public function featuredMatches(int $tournamentId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}/featured-events");

        return $data;
    }

    /**
     * Tournament cup tree for the selected season (Python {@code tournament_cuptree}).
     *
     * @return array<string, mixed>
     */
    public function tournamentCuptree(int $tournamentId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}/season/{$seasonId}/cuptrees");

        return $data;
    }

    /**
     * Next matches for the selected tournament and season
     * (Python {@code next_tournament_matches}).
     *
     * @return array<string, mixed>
     */
    public function nextTournamentMatches(int $tournamentId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}/season/{$seasonId}/events/next/0");

        return $data;
    }

    /**
     * Last matches for the selected tournament and season
     * (Python {@code last_tournament_matches}).
     *
     * @return array<string, mixed>
     */
    public function lastTournamentMatches(int $tournamentId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}/season/{$seasonId}/events/last/0");

        return $data;
    }

    /**
     * All matches for the selected tournament and season
     * (Python {@code tournament_matches}).
     *
     * @return array<string, mixed>
     */
    public function tournamentMatches(int $tournamentId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}/season/{$seasonId}/events");

        return $data;
    }

    /**
     * Match info (Python {@code get_match}).
     *
     * @return array<string, mixed>
     */
    public function getMatch(int $matchId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$matchId}/esports-games");

        return $data;
    }

    /**
     * Further match info, the rounds (Python {@code match_rounds}).
     *
     * @return array<string, mixed>
     */
    public function matchRounds(int $matchId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/esports-game/{$matchId}/rounds");

        return $data;
    }

    /**
     * Match lineups (Python {@code lineups}).
     *
     * @return array<string, mixed>
     */
    public function lineups(int $matchId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/esports-game/{$matchId}/lineups");

        return $data;
    }

    /**
     * Team streaks for the selected match (Python {@code team_streaks}).
     *
     * @return array<string, mixed>
     */
    public function teamStreaks(int $matchId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/esports-game/{$matchId}/team-streaks");

        return $data;
    }

    /**
     * Match highlights (Python {@code highlights}).
     *
     * @return array<string, mixed>
     */
    public function highlights(int $matchId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/esports-game/{$matchId}/highlights");

        return $data;
    }

    /**
     * All currently live e-sport events (Python {@code live_matches}).
     *
     * @return array<string, mixed>
     */
    public function liveMatches(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/esports/events/live');

        return $data;
    }
}
