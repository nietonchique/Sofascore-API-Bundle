<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use Nietonchique\SofascoreApiBundle\Dto\Tournament;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * League (unique tournament) endpoint group. Faithful port of the Python
 * {@code league.py} module; the league id is bound at construction.
 */
final class League extends AbstractEndpoint
{
    private const BASE_URL = 'https://www.sofascore.com/api/v1';

    public function __construct(
        TransportInterface $transport,
        Enums $enums,
        private readonly int $leagueId,
    ) {
        parent::__construct($transport, $enums);
    }

    /**
     * Fetch general information about the league (the bound league id) and
     * return it as a {@see Tournament} (Python {@code get_league}).
     *
     * Named {@code getLeague} rather than {@code get} because the inherited
     * request helper already occupies {@code get(string, array): array}.
     */
    public function getLeague(): Tournament
    {
        $data = $this->get("/unique-tournament/{$this->leagueId}");
        $tournament = $data['uniqueTournament'] ?? null;

        return Tournament::fromArray(\is_array($tournament) ? $tournament : $data);
    }

    /**
     * Object-shaped request: the SofaScore endpoints used here return JSON
     * objects, so narrow the inherited helper's {@code array<array-key, mixed>}
     * to {@code array<string, mixed>} in one place.
     *
     * @param array<string, scalar|null> $query
     *
     * @return array<string, mixed>
     */
    private function getObject(string $endpoint, array $query = []): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get($endpoint, $query);

        return $data;
    }

    /**
     * Fetch all available seasons for the league.
     *
     * @return array<array-key, mixed>
     */
    public function getSeasons(): array
    {
        $data = $this->get("/unique-tournament/{$this->leagueId}/seasons");
        $seasons = $data['seasons'] ?? [];

        return \is_array($seasons) ? $seasons : [];
    }

    /**
     * Returns the current season for the selected league, or null if none.
     *
     * @return array<array-key, mixed>|null
     */
    public function currentSeason(): ?array
    {
        $seasonObj = $this->getSeasons();
        $first = $seasonObj[0] ?? null;

        return \is_array($first) ? $first : null;
    }

    /**
     * Fetch statistical information about the league for a specific season.
     *
     * @return array<string, mixed>
     */
    public function getInfo(int $season): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/info");
    }

    /**
     * Fetch the top players per game for a specific season.
     *
     * @return array<array-key, mixed>
     */
    public function topPlayersPerGame(int $season): array
    {
        return $this->get("/unique-tournament/{$this->leagueId}/season/{$season}/top-players-per-game/all/overall");
    }

    /**
     * Get the league image URL.
     */
    public function getImage(string $imageType = 'dark'): string
    {
        return self::BASE_URL."/unique-tournament/{$this->leagueId}/image/{$imageType}";
    }

    /**
     * Get top players for a specific season.
     *
     * @return array<string, mixed>
     */
    public function topPlayers(int $season): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/top-players/overall");
    }

    /**
     * Get top teams for a specific season.
     *
     * @return array<string, mixed>
     */
    public function topTeams(int $season): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/top-teams/overall");
    }

    /**
     * Get the latest match highlights for the league.
     *
     * @return array<string, mixed>
     */
    public function getLatestHighlights(): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/media");
    }

    /**
     * Get the current league standings for a specific season.
     *
     * @return array<string, mixed>
     */
    public function standings(int $season): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/standings/total");
    }

    /**
     * Get the current league standings for home games in a specific season.
     *
     * @return array<string, mixed>
     */
    public function standingsHome(int $season): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/standings/home");
    }

    /**
     * Get the current league standings for away games in a specific season.
     *
     * @return array<string, mixed>
     */
    public function standingsAway(int $season): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/standings/away");
    }

    /**
     * Get the player of the season for a specific season.
     *
     * @return array<string, mixed>
     */
    public function playerOfTheSeason(int $season): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/player-of-the-season");
    }

    /**
     * Get the featured game for the league.
     *
     * @return array<string, mixed>
     */
    public function featuredGames(): array
    {
        return $this->getObject("/unique-tournaments/{$this->leagueId}/featured-events");
    }

    /**
     * Get the available team of the week rounds for a specific season.
     *
     * @return array<string, mixed>
     */
    public function totwRounds(int $season): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/team-of-the-week/rounds");
    }

    /**
     * Get the team of the week for a specific round in a season.
     *
     * @return array<string, mixed>
     */
    public function totw(int $season, int $round): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/team-of-the-week/{$round}");
    }

    /**
     * Get the available rounds for a specific season.
     *
     * @return array<string, mixed>
     */
    public function rounds(int $season): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/rounds");
    }

    /**
     * Get the current round for a specific season.
     */
    public function currentRound(int $season): ?int
    {
        $data = $this->get("/unique-tournament/{$this->leagueId}/season/{$season}/rounds");
        $currentRound = $data['currentRound'] ?? null;
        $round = \is_array($currentRound) ? ($currentRound['round'] ?? null) : null;

        return \is_int($round) ? $round : null;
    }

    /**
     * Fetch the fixtures for a specific season and round.
     *
     * @return array<string, mixed>
     */
    public function fixtures(int $season, int $round): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$season}/round/{$round}");
    }

    /**
     * Fetch the next fixtures (status code 0) for the current season and round,
     * sorted by start timestamp ascending. Returns null when there are none.
     *
     * @return list<array<array-key, mixed>>|null
     */
    public function nextFixtures(): ?array
    {
        $seasonObj = $this->currentSeason();
        $seasonId = $seasonObj['id'] ?? 0;
        $season = \is_int($seasonId) ? $seasonId : 0;
        $round = $this->currentRound($season);

        $data = $this->get("/unique-tournament/{$this->leagueId}/season/{$season}/events/round/{$round}");
        $fixtures = $this->filterEventsByStatusCode($data, 0);

        if ([] === $fixtures) {
            return null;
        }

        usort($fixtures, static fn (array $a, array $b): int => self::timestamp($a) <=> self::timestamp($b));

        return $fixtures;
    }

    /**
     * Fetch the last fixtures (status code 100) for the current season and
     * round, or the previous round when the current one has none. Sorted by
     * start timestamp descending. Returns null when there are none.
     *
     * @return list<array<array-key, mixed>>|null
     */
    public function lastFixtures(): ?array
    {
        $seasonObj = $this->currentSeason();
        $seasonId = $seasonObj['id'] ?? 0;
        $season = \is_int($seasonId) ? $seasonId : 0;
        $roundObj = $this->currentRound($season) ?? 0;

        $data = $this->get("/unique-tournament/{$this->leagueId}/season/{$season}/events/round/{$roundObj}");
        $fixtures = $this->filterEventsByStatusCode($data, 100);

        if ([] !== $fixtures) {
            usort($fixtures, static fn (array $a, array $b): int => self::timestamp($b) <=> self::timestamp($a));

            return $fixtures;
        }

        $round = $roundObj > 1 ? $roundObj - 1 : 1;

        $data = $this->get("/unique-tournament/{$this->leagueId}/season/{$season}/events/round/{$round}");
        $lastFixtures = $this->filterEventsByStatusCode($data, 100);
        usort($lastFixtures, static fn (array $a, array $b): int => self::timestamp($b) <=> self::timestamp($a));

        return [] !== $lastFixtures ? $lastFixtures : null;
    }

    /**
     * Filter a fixtures payload to the events whose {@code status.code} equals
     * the given value (mirrors the Python list comprehensions).
     *
     * @param array<array-key, mixed> $data
     *
     * @return list<array<array-key, mixed>>
     */
    private function filterEventsByStatusCode(array $data, int $statusCode): array
    {
        $events = $data['events'] ?? [];
        if (!\is_array($events)) {
            return [];
        }

        $matched = [];
        foreach ($events as $event) {
            if (!\is_array($event)) {
                continue;
            }
            $status = $event['status'] ?? null;
            $code = \is_array($status) ? ($status['code'] ?? null) : null;
            if ($statusCode === $code) {
                $matched[] = $event;
            }
        }

        return $matched;
    }

    /**
     * Extract an event's {@code startTimestamp} as an int for sorting.
     *
     * @param array<array-key, mixed> $event
     */
    private static function timestamp(array $event): int
    {
        $ts = $event['startTimestamp'] ?? 0;

        return \is_int($ts) ? $ts : 0;
    }

    /**
     * Retrieves the cup tree structure for a given season.
     *
     * @return array<string, mixed>
     */
    public function cupTree(int $seasonId): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$seasonId}/cuptrees");
    }

    /**
     * Fetch the cup fixtures for the given season and round.
     *
     * @return array<string, mixed>
     */
    public function cupFixturesPerRound(int $seasonId, int $roundId): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$seasonId}/events/round/{$roundId}/slug/round-{$roundId}");
    }

    /**
     * Fetch the league fixtures for the given season and round.
     *
     * @return array<string, mixed>
     */
    public function leagueFixturesPerRound(int $seasonId, int $roundId): array
    {
        return $this->getObject("/unique-tournament/{$this->leagueId}/season/{$seasonId}/events/round/{$roundId}");
    }

    /**
     * Retrieves all tournaments for a selected sport category.
     *
     * @return array<string, mixed>
     */
    public function leagues(int $categoryId): array
    {
        return $this->getObject("/category/{$categoryId}/unique-tournaments");
    }

    /**
     * Retrieves the tournament info for an arbitrary league/season.
     *
     * @return array<string, mixed>
     */
    public function leagueInfo(int $leagueId, int $seasonId): array
    {
        return $this->getObject("/unique-tournaments/{$leagueId}/season/{$seasonId}/info");
    }

    /**
     * Retrieves the tournament for an arbitrary league id (Python's second
     * {@code get_league(league_id)} overload).
     *
     * @return array<string, mixed>
     */
    public function getLeagueById(int $leagueId): array
    {
        return $this->getObject("/unique-tournaments/{$leagueId}");
    }
}
