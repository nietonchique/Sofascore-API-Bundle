<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use Nietonchique\SofascoreApiBundle\Dto\Team as TeamDto;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * Team endpoint group. Faithful port of the Python {@code team.py} module; the
 * team id is bound at construction.
 */
final class Team extends AbstractEndpoint
{
    public function __construct(
        TransportInterface $transport,
        Enums $enums,
        private readonly int $teamId,
    ) {
        parent::__construct($transport, $enums);
    }

    /**
     * Detailed information about the team as a typed DTO (Python {@code get_team}).
     *
     * Named {@code getTeam()} (not {@code get()}) because the inherited request
     * helper already occupies {@code get(string, array): array}. The full raw
     * payload remains available via the returned DTO's {@code raw} / {@code toArray()}.
     */
    public function getTeam(): TeamDto
    {
        $data = $this->get("/team/{$this->teamId}");
        $team = $data['team'] ?? null;

        return TeamDto::fromArray(\is_array($team) ? $team : $data);
    }

    /**
     * URL of the team's image (Python {@code image}).
     */
    public function image(): string
    {
        return "https://img.sofascore.com/api/v1/team/{$this->teamId}/image";
    }

    /**
     * Performance data of the team (Python {@code performance}).
     *
     * @return array<string, mixed>
     */
    public function performance(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$this->teamId}/performance");

        return $data;
    }

    /**
     * Players who have transferred into the team (Python {@code transfers_in}).
     *
     * @return array<int, mixed>
     */
    public function transfersIn(): array
    {
        $data = $this->get("/team/{$this->teamId}/transfers");
        /** @var array<int, mixed> $transfers */
        $transfers = $data['transfersIn'];

        return array_reverse($transfers);
    }

    /**
     * Players who have transferred out of the team (Python {@code transfers_out}).
     *
     * @return array<int, mixed>
     */
    public function transfersOut(): array
    {
        $data = $this->get("/team/{$this->teamId}/transfers");
        /** @var array<int, mixed> $transfers */
        $transfers = $data['transfersOut'];

        return array_reverse($transfers);
    }

    /**
     * Next fixtures for the team (Python {@code next_fixtures}).
     *
     * @return array<int, mixed>
     */
    public function nextFixtures(): array
    {
        $data = $this->get("/team/{$this->teamId}/events/next/0");
        /** @var array<int, mixed> $events */
        $events = $data['events'];

        return array_reverse($events);
    }

    /**
     * Last fixtures for the team (Python {@code last_fixtures}).
     *
     * @return array<int, mixed>
     */
    public function lastFixtures(): array
    {
        $data = $this->get("/team/{$this->teamId}/events/last/0");
        /** @var array<int, mixed> $events */
        $events = $data['events'];

        return array_reverse($events);
    }

    /**
     * Seasons in which the team has participated (Python {@code seasons}).
     *
     * @return array<string, mixed>
     */
    public function seasons(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$this->teamId}/team-statistics/seasons");

        return $data;
    }

    /**
     * Squad of the team (Python {@code squad}).
     *
     * @return array<string, mixed>
     */
    public function squad(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$this->teamId}/players");

        return $data;
    }

    /**
     * Top players of the team for a league and season (Python {@code top_players}).
     *
     * @return array<string, mixed>
     */
    public function topPlayers(int $leagueId, int $season): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$this->teamId}/unique-tournament/{$leagueId}/season/{$season}/top-players/overall");

        return $data;
    }

    /**
     * League statistics of the team for a league and season (Python {@code league_stats}).
     *
     * @return array<string, mixed>
     */
    public function leagueStats(int $leagueId, int $season): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$this->teamId}/unique-tournament/{$leagueId}/season/{$season}/statistics/overall");

        return $data;
    }

    /**
     * Latest highlights for the team (Python {@code latest_highlights}).
     *
     * @return array<string, mixed>
     */
    public function latestHighlights(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$this->teamId}/media");

        return $data;
    }

    /**
     * Performance graph data for the team in a league and season (Python {@code performance_graph}).
     *
     * @return array<string, mixed>
     */
    public function performanceGraph(int $leagueId, int $season): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-tournament/{$leagueId}/season/{$season}/team/{$this->teamId}/team-performance-graph-data");

        return $data;
    }

    /**
     * Team's nearest matches (Python {@code near_events}).
     *
     * @return array<string, mixed>
     */
    public function nearEvents(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$this->teamId}/near-events");

        return $data;
    }
}
