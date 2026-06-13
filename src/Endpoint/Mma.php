<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use DateTimeImmutable;

/**
 * MMA endpoint group. Faithful port of the Python {@code mma.py} module; no id
 * is bound at construction, every method takes its ids as parameters.
 */
final class Mma extends AbstractEndpoint
{
    /**
     * Total count of today's MMA fights and how many are currently live
     * (Python {@code total_fights}).
     *
     * @return array<string, mixed>
     */
    public function totalFights(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $mma */
        $mma = $data['mma'] ?? [];

        return $mma;
    }

    /**
     * All currently live MMA events (Python {@code live_fights}).
     *
     * @return array<string, mixed>
     */
    public function liveFights(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/mma/events/live');

        return $data;
    }

    /**
     * Scheduled fixtures for a sport on a specific date (Python {@code fights_by_date}).
     *
     * @return array<string, mixed>
     */
    public function fightsByDate(string $sport = 'mma', ?string $date = null): array
    {
        $date ??= $this->today();
        $sport = $this->enums->assertSport($sport);

        /** @var array<string, mixed> $data */
        $data = $this->get("/sport/{$sport}/scheduled-events/{$date}");

        return $data;
    }

    /**
     * Career statistics for a fighter (Python {@code fighter_career_stats}).
     *
     * @return array<string, mixed>
     */
    public function fighterCareerStats(int $fighterId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$fighterId}/career-statistics");

        return $data;
    }

    /**
     * Upcoming fights for a fighter (Python {@code fighter_next_fights}).
     *
     * @return array<int, mixed>
     */
    public function fighterNextFights(int $fighterId): array
    {
        $data = $this->get("/team/{$fighterId}/events/next/0");
        /** @var array<int, mixed> $events */
        $events = $data['events'];

        return array_reverse($events);
    }

    /**
     * Previous fights for a fighter (Python {@code fighter_last_fights}).
     *
     * @return array<int, mixed>
     */
    public function fighterLastFights(int $fighterId): array
    {
        $data = $this->get("/team/{$fighterId}/events/last/0");
        /** @var array<int, mixed> $events */
        $events = $data['events'];

        return array_reverse($events);
    }

    /**
     * Rankings and previous fights for a fighter (Python {@code fighter_rankings}).
     *
     * @return array<string, mixed>
     */
    public function fighterRankings(int $fighterId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/rankings/team/{$fighterId}");

        return $data;
    }

    /**
     * Info about a fighter (Python {@code fighter_info}).
     *
     * @return array<string, mixed>
     */
    public function fighterInfo(int $fighterId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$fighterId}");

        return $data;
    }

    /**
     * MMA main events for a date or today (Python {@code main_events_date}).
     *
     * @return array<string, mixed>
     */
    public function mainEventsDate(?string $date = null): array
    {
        $date ??= $this->today();

        /** @var array<string, mixed> $data */
        $data = $this->get("/sport/mma/main-events/{$date}/extended");

        return $data;
    }

    /**
     * MMA main events for an organisation in a month or the current month
     * (Python {@code main_events_month}).
     *
     * @return array<string, mixed>
     */
    public function mainEventsMonth(int $organisationId, ?string $date = null): array
    {
        $date ??= (new DateTimeImmutable())->format('Y-m');

        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$organisationId}/scheduled-mma-main-events/{$date}");

        return $data;
    }

    /**
     * Active MMA tournaments such as UFC and BELLATOR (Python {@code mma_tournaments}).
     *
     * @return array<string, mixed>
     */
    public function mmaTournaments(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/category/1708/unique-tournaments');

        return $data;
    }

    /**
     * Months that have fights for a tournament (Python {@code mma_tournaments_months}).
     *
     * @return array<string, mixed>
     */
    public function mmaTournamentsMonths(int $tournamentId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/calendar/unique-tournament/{$tournamentId}/0/months-with-events");

        return $data;
    }

    /**
     * Info about a tournament (Python {@code tournament_info}).
     *
     * @return array<string, mixed>
     */
    public function tournamentInfo(int $tournamentId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}");

        return $data;
    }

    /**
     * URL of the fighter's image (Python {@code fighter_image}).
     */
    public function fighterImage(int $fighterId): string
    {
        return "https://img.sofascore.com/api/v1/team/{$fighterId}/image";
    }

    /**
     * URL of the tournament's image (Python {@code tournament_image}).
     */
    public function tournamentImage(int $tournamentId): string
    {
        return "https://img.sofascore.com/api/v1/unique-tournament/{$tournamentId}/image/dark";
    }

    /**
     * Ranking summary for a tournament (Python {@code ranking_summary}).
     *
     * @return array<string, mixed>
     */
    public function rankingSummary(int $tournamentId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$tournamentId}/summary");

        return $data;
    }

    /**
     * Full ranking data for a ranking id (Python {@code rankings}).
     *
     * @return array<string, mixed>
     */
    public function rankings(int $rankingId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/rankings/{$rankingId}");

        return $data;
    }
}
