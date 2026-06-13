<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * Motorsport endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class Motorsport extends AbstractEndpoint
{
    /**
     * Total count of today's motorsport races and how many are currently live
     * (Python {@code total_races}).
     *
     * @return array<string, mixed>
     */
    public function totalRaces(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $motorsport */
        $motorsport = $data['motorsport'] ?? [];

        return $motorsport;
    }

    /**
     * All motorsport categories such as Rally, F1 (Python {@code categories}).
     *
     * @return array<string, mixed>
     */
    public function categories(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/motorsport/categories');

        return $data;
    }

    /**
     * All currently live motorsport events (Python {@code live_races}).
     *
     * @return array<string, mixed>
     */
    public function liveRaces(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/motorsport/events/live');

        return $data;
    }

    /**
     * The featured motorsport races (Python {@code featured_races}).
     *
     * @return array<string, mixed>
     */
    public function featuredRaces(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/stage/sport/motorsport/featured');

        return $data;
    }

    /**
     * Season details for a specific motorsport category (Python {@code seasons}).
     *
     * @return array<string, mixed>
     */
    public function seasons(int $motorsportId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/unique-stage/{$motorsportId}/seasons");

        return $data;
    }

    /**
     * Current season id for the selected motorsport (Python {@code current_season}).
     *
     * Filters the seasons to the one whose timeframe contains "now" and returns
     * its {@code uniqueStage.id}, or {@code null} when none is active.
     */
    public function currentSeason(int $motorsportId): ?int
    {
        $data = $this->get("/unique-stage/{$motorsportId}/seasons");
        /** @var array<int, mixed> $seasons */
        $seasons = $data['seasons'] ?? [];
        $now = time();

        $seasonObj = array_values(array_filter(
            $seasons,
            static function (mixed $season) use ($now): bool {
                if (!\is_array($season)) {
                    return false;
                }
                $start = $season['startDateTimestamp'] ?? null;
                $end = $season['endDateTimestamp'] ?? null;

                return \is_int($start) && \is_int($end) && $start <= $now && $now <= $end;
            },
        ));

        $first = $seasonObj[0] ?? null;
        if (!\is_array($first)) {
            return null;
        }

        $uniqueStage = $first['uniqueStage'] ?? [];
        if (!\is_array($uniqueStage)) {
            return null;
        }

        $id = $uniqueStage['id'] ?? null;

        return \is_int($id) ? $id : null;
    }

    /**
     * All races for the selected season (Python {@code races}).
     *
     * @return array<string, mixed>
     */
    public function races(int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/stage/{$seasonId}/substages");

        return $data;
    }

    /**
     * Detailed information about a specific race stage (Python {@code race_info}).
     *
     * @return array<string, mixed>
     */
    public function raceInfo(int $stageId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/stage/{$stageId}");

        return $data;
    }

    /**
     * Current standings of drivers for a season (Python {@code driver_rankings}).
     *
     * @return array<string, mixed>
     */
    public function driverRankings(int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/stage/{$seasonId}/standings/competitor");

        return $data;
    }

    /**
     * Current standings of teams for a season (Python {@code team_rankings}).
     *
     * @return array<string, mixed>
     */
    public function teamRankings(int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/stage/{$seasonId}/standings/team");

        return $data;
    }

    /**
     * Driver rankings for a specific race (Python {@code race_results}).
     *
     * @return array<string, mixed>
     */
    public function raceResults(int $stageId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/stage/{$stageId}/standings/competitor");

        return $data;
    }

    /**
     * URL of the race track image (Python {@code race_image}).
     */
    public function raceImage(int $stageId): string
    {
        return "https://img.sofascore.com/api/v1/stage/{$stageId}/image";
    }

    /**
     * URL of the team image (Python {@code team_image}).
     */
    public function teamImage(int $teamId): string
    {
        return "https://img.sofascore.com/api/v1/team/{$teamId}/image";
    }

    /**
     * URL of the driver image (Python {@code driver_image}).
     */
    public function driverImage(int $driverId): string
    {
        return "https://img.sofascore.com/api/v1/team/{$driverId}/image";
    }

    /**
     * Detailed information about a driver (Python {@code driver_info}).
     *
     * @return array<string, mixed>
     */
    public function driverInfo(int $driverId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$driverId}");

        return $data;
    }

    /**
     * Detailed information about a team (Python {@code team_info}).
     *
     * @return array<string, mixed>
     */
    public function teamInfo(int $teamId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$teamId}");

        return $data;
    }

    /**
     * Seasons in which a driver has participated (Python {@code driver_seasons}).
     *
     * @return array<string, mixed>
     */
    public function driverSeasons(int $driverId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$driverId}/stage-seasons");

        return $data;
    }

    /**
     * Seasons in which a team has participated (Python {@code team_seasons}).
     *
     * @return array<string, mixed>
     */
    public function teamSeasons(int $teamId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$teamId}/stage-seasons");

        return $data;
    }

    /**
     * Races in which the driver participated for the given season (Python {@code driver_races}).
     *
     * @return array<string, mixed>
     */
    public function driverRaces(int $driverId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$driverId}/stage-season/{$seasonId}/races");

        return $data;
    }

    /**
     * Races in which the team participated for the given season (Python {@code team_races}).
     *
     * @return array<string, mixed>
     */
    public function teamRaces(int $teamId, int $seasonId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/team/{$teamId}/stage-season/{$seasonId}/races");

        return $data;
    }
}
