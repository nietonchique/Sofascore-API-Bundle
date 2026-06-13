<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * Rugby endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class Rugby extends AbstractEndpoint
{
    /**
     * Total count of today's rugby matches and how many are live (Python {@code total_matches}).
     *
     * @return array<string, mixed>
     */
    public function totalMatches(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $rugby */
        $rugby = $data['rugby'] ?? [];

        return $rugby;
    }

    /**
     * All the rugby tournaments (Python {@code all_tournaments}).
     *
     * @return array<string, mixed>
     */
    public function allTournaments(string $countryCode = 'GB'): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/config/default-unique-tournaments/'.strtoupper($countryCode).'/rugby');

        return $data;
    }

    /**
     * All the rugby categories (Python {@code categories}).
     *
     * @return array<string, mixed>
     */
    public function categories(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/rugby/categories');

        return $data;
    }

    /**
     * Scheduled fixtures for a given sport on a specific date (Python {@code matches_by_date}).
     *
     * @return array<string, mixed>
     */
    public function matchesByDate(string $sport = 'rugby', ?string $date = null): array
    {
        $date ??= $this->today();
        $sport = $this->enums->assertSport($sport);

        /** @var array<string, mixed> $data */
        $data = $this->get("/sport/{$sport}/scheduled-events/{$date}");

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
}
