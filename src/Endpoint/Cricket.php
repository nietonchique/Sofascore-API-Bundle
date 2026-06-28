<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * Cricket endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class Cricket extends AbstractEndpoint
{
    /**
     * Total count of today's cricket matches and how many are live (Python {@code total_matches}).
     *
     * @return array<string, mixed>
     */
    public function totalMatches(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $cricket */
        $cricket = $data['cricket'] ?? [];

        return $cricket;
    }

    /**
     * All the cricket tournaments (Python {@code all_tournaments}).
     *
     * @return array<string, mixed>
     */
    public function allTournaments(string $countryCode = 'GB'): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/config/default-unique-tournaments/'.strtoupper($countryCode).'/cricket');

        return $data;
    }

    /**
     * All the cricket categories such as the countries (Python {@code categories}).
     *
     * @return array<string, mixed>
     */
    public function categories(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/cricket/categories');

        return $data;
    }

    /**
     * Scheduled fixtures for a given sport on a specific date (Python {@code matches_by_date}).
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
     * The current innings for the given game (Python {@code match_innings}).
     *
     * @return array<string, mixed>
     */
    public function matchInnings(int $matchId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$matchId}/innings");

        return $data;
    }
}
