<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * Search endpoint group. Faithful port of the Python {@code search.py} module;
 * the search term and page are bound at construction (see Python {@code Search(api, search_string, page=0)}).
 */
final class Search extends AbstractEndpoint
{
    public function __construct(
        TransportInterface $transport,
        Enums $enums,
        private readonly string $searchString,
        private readonly int $page = 0,
    ) {
        parent::__construct($transport, $enums);
    }

    /**
     * Search across all categories: teams, players, matches, leagues, managers (Python {@code search_all}).
     *
     * Without a sport, the raw payload is returned. With a sport, the results are
     * filtered down to that sport's id and re-wrapped as {@code {"results": [...]}},
     * mirroring the Python post-processing exactly.
     *
     * @return array<string, mixed>|array{results: list<array<array-key, mixed>>}
     */
    public function searchAll(?string $sport = null): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/search/all/', ['q' => $this->searchString, 'page' => $this->page]);

        if (null === $sport) {
            return $data;
        }

        return ['results' => $this->filterBySport($data, $sport)];
    }

    /**
     * Search specifically for matches (Python {@code search_match}).
     *
     * @return array<string, mixed>|array{results: list<array<array-key, mixed>>}
     */
    public function searchMatch(?string $sport = null): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/search/events/', ['q' => $this->searchString, 'page' => $this->page]);

        if (null === $sport) {
            return $data;
        }

        return ['results' => $this->filterBySport($data, $sport)];
    }

    /**
     * Search specifically for players (Python {@code search_players}).
     *
     * @return array<string, mixed>|array{results: list<array<array-key, mixed>>}
     */
    public function searchPlayers(?string $sport = null): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/search/player-team-persons/', ['q' => $this->searchString, 'page' => $this->page]);

        if (null === $sport) {
            return $data;
        }

        return ['results' => $this->filterBySport($data, $sport)];
    }

    /**
     * Search specifically for teams (Python {@code search_teams}).
     *
     * @return array<string, mixed>|array{results: list<array<array-key, mixed>>}
     */
    public function searchTeams(?string $sport = null): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/search/teams/', ['q' => $this->searchString, 'page' => $this->page]);

        if (null === $sport) {
            return $data;
        }

        return ['results' => $this->filterBySport($data, $sport)];
    }

    /**
     * Search specifically for leagues (Python {@code search_leagues}).
     *
     * @return array<string, mixed>|array{results: list<array<array-key, mixed>>}
     */
    public function searchLeagues(?string $sport = null): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/search/unique-tournaments/', ['q' => $this->searchString, 'page' => $this->page]);

        if (null === $sport) {
            return $data;
        }

        return ['results' => $this->filterBySport($data, $sport)];
    }

    /**
     * Sport id of a single search entry, by its {@code type} (Python {@code get_sport_id}).
     *
     * Returns {@code null} for unknown entry shapes, mirroring the Python fall-through.
     *
     * @param array<array-key, mixed> $entry
     */
    public function getSportId(array $entry): ?int
    {
        $entity = \is_array($entry['entity'] ?? null) ? $entry['entity'] : [];

        $id = match ($entry['type'] ?? null) {
            'team' => $this->dig($entity, ['sport', 'id']),
            'player' => $this->dig($entity, ['team', 'sport', 'id']),
            'event' => $this->dig($entity, ['tournament', 'category', 'sport', 'id']),
            'uniqueTournament' => $this->dig($entity, ['category', 'sport', 'id']),
            default => null,
        };

        return \is_int($id) ? $id : null;
    }

    /**
     * Keep only the {@code results} entries whose sport id matches the validated sport.
     *
     * @param array<string, mixed> $data
     *
     * @return list<array<array-key, mixed>>
     */
    private function filterBySport(array $data, string $sport): array
    {
        $sportId = $this->enums->sportId($this->enums->assertSport($sport));

        /** @var list<mixed> $results */
        $results = \is_array($data['results'] ?? null) ? array_values($data['results']) : [];

        $kept = [];
        foreach ($results as $entry) {
            if (\is_array($entry) && $this->getSportId($entry) === $sportId) {
                $kept[] = $entry;
            }
        }

        return $kept;
    }

    /**
     * Walk a nested associative array along {@code $path}; return the leaf or {@code null}.
     *
     * @param array<array-key, mixed> $data
     * @param list<string>            $path
     */
    private function dig(array $data, array $path): mixed
    {
        $current = $data;
        foreach ($path as $key) {
            if (!\is_array($current) || !\array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }

        return $current;
    }
}
