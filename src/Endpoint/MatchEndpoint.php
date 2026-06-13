<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use Nietonchique\SofascoreApiBundle\Dto\Event;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * Match (event) endpoint group. Faithful port of the Python {@code match.py}
 * module; the match id is bound at construction (see Python {@code Match(api, match_id=None)}).
 *
 * Named with an "Endpoint" suffix because {@code Match} is a reserved keyword in
 * PHP. The convenient accessor is {@code SofascoreClient::match()}.
 */
final class MatchEndpoint extends AbstractEndpoint
{
    public function __construct(
        TransportInterface $transport,
        Enums $enums,
        private readonly ?int $matchId = null,
    ) {
        parent::__construct($transport, $enums);
    }

    /**
     * Total count of today's football games and how many are currently live
     * (Python {@code total_games}).
     *
     * @return array<string, mixed>
     */
    public function totalGames(): array
    {
        $data = $this->get('/sport/0/event-count');
        /** @var array<string, mixed> $football */
        $football = $data['football'] ?? [];

        return $football;
    }

    /**
     * All currently live football games (Python {@code live_games}).
     *
     * @return array<string, mixed>
     */
    public function liveGames(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/sport/football/events/live');

        return $data;
    }

    /**
     * Fixtures for today or a specific date (Python {@code games_by_date}).
     *
     * @return array<string, mixed>
     */
    public function gamesByDate(string $sport, ?string $date = null): array
    {
        $date ??= $this->today();
        $sport = $this->enums->assertSport($sport);

        /** @var array<string, mixed> $data */
        $data = $this->get("/sport/{$sport}/scheduled-events/{$date}");

        return $data;
    }

    /**
     * All odds for the match (Python {@code match_odds}).
     *
     * @return array<string, mixed>
     */
    public function matchOdds(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/odds/1/all");

        return $data;
    }

    /**
     * Top team win streaks across all odds (Python {@code top_team_streaks}).
     *
     * @return array<string, mixed>
     */
    public function topTeamStreaks(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/odds/top-team-streaks/wins/all');

        return $data;
    }

    /**
     * Featured odds for the match (Python {@code featured_odds}).
     *
     * @return array<string, mixed>
     */
    public function featuredOdds(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/odds/1/featured");

        return $data;
    }

    /**
     * Head-to-head data for the match (Python {@code h2h}).
     *
     * @return array<string, mixed>
     */
    public function h2h(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/h2h");

        return $data;
    }

    /**
     * Match incidents (goals, cards, substitutions, etc.) (Python {@code incidents}).
     *
     * @return array<string, mixed>
     */
    public function incidents(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/incidents");

        return $data;
    }

    /**
     * Best away-team players for the match (Python {@code best_away_players}).
     *
     * @return array<int, mixed>|null
     */
    public function bestAwayPlayers(): ?array
    {
        $data = $this->get("/event/{$this->matchId}/best-players/summary");
        /** @var array<int, mixed>|null $players */
        $players = $data['bestAwayTeamPlayers'] ?? null;

        return $players;
    }

    /**
     * Best home-team players for the match (Python {@code best_home_players}).
     *
     * @return array<int, mixed>|null
     */
    public function bestHomePlayers(): ?array
    {
        $data = $this->get("/event/{$this->matchId}/best-players/summary");
        /** @var array<int, mixed>|null $players */
        $players = $data['bestHomeTeamPlayers'] ?? null;

        return $players;
    }

    /**
     * Man of the match (Python {@code motm}).
     *
     * @return array<string, mixed>|null
     */
    public function motm(): ?array
    {
        $data = $this->get("/event/{$this->matchId}/best-players/summary");
        /** @var array<string, mixed>|null $player */
        $player = $data['playerOfTheMatch'] ?? null;

        return $player;
    }

    /**
     * Vote counts for the match (Python {@code votes}).
     *
     * @return array<string, mixed>
     */
    public function votes(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/votes");

        return $data;
    }

    /**
     * Pre-match form for both teams (Python {@code pre_match_form}).
     *
     * @return array<string, mixed>
     */
    public function preMatchForm(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/pregame-form");

        return $data;
    }

    /**
     * TV channels broadcasting the match by country (Python {@code match_channels}).
     *
     * @return array<string, mixed>
     */
    public function matchChannels(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/tv/event/{$this->matchId}/country-channels");

        return $data;
    }

    /**
     * Name of a specific TV channel for the match, or null if not found
     * (Python {@code get_channel}).
     */
    public function getChannel(int $channelId): ?string
    {
        $data = $this->get("/tv/channel/{$channelId}/event/{$this->matchId}/votes");

        $votes = $data['tvChannelVotes'] ?? [];
        $channel = \is_array($votes) ? ($votes['tvChannel'] ?? []) : [];
        $name = \is_array($channel) ? ($channel['name'] ?? null) : null;

        return \is_string($name) ? $name : null;
    }

    /**
     * Broadcast schedule for a specific TV channel (Python {@code channel_schedule}).
     *
     * @return array<string, mixed>
     */
    public function channelSchedule(int $channelId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/tv/channel/{$channelId}/schedule");

        return $data;
    }

    /**
     * Managers of both teams for the match (Python {@code managers}).
     *
     * @return array<string, mixed>
     */
    public function managers(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/managers");

        return $data;
    }

    /**
     * Confirmed lineup of the home team (Python {@code lineups_home}).
     *
     * @return array{
     *     confirmed: mixed,
     *     formation: mixed,
     *     player_colour: mixed,
     *     goalkeeper_colour: mixed,
     *     missing_players: mixed,
     *     starters: list<mixed>,
     *     substitutes: list<mixed>
     * }
     */
    public function lineupsHome(): array
    {
        $data = $this->get("/event/{$this->matchId}/lineups");
        $home = $data['home'] ?? [];
        $players = \is_array($home) ? ($home['players'] ?? []) : [];
        $players = \is_array($players) ? $players : [];

        return [
            'confirmed' => $data['confirmed'] ?? null,
            'formation' => \is_array($home) ? ($home['formation'] ?? null) : null,
            'player_colour' => \is_array($home) ? ($home['playerColor'] ?? null) : null,
            'goalkeeper_colour' => \is_array($home) ? ($home['goalkeeperColor'] ?? null) : null,
            'missing_players' => \is_array($home) ? ($home['missingPlayers'] ?? null) : null,
            'starters' => array_values(array_filter($players, static fn ($entry): bool => !(\is_array($entry) && (bool) ($entry['substitute'] ?? false)))),
            'substitutes' => array_values(array_filter($players, static fn ($entry): bool => \is_array($entry) && (bool) ($entry['substitute'] ?? false))),
        ];
    }

    /**
     * Confirmed lineup of the away team (Python {@code lineups_away}).
     *
     * @return array{
     *     confirmed: mixed,
     *     formation: mixed,
     *     player_colour: mixed,
     *     goalkeeper_colour: mixed,
     *     missing_players: mixed,
     *     starters: list<mixed>,
     *     substitutes: list<mixed>
     * }
     */
    public function lineupsAway(): array
    {
        $data = $this->get("/event/{$this->matchId}/lineups");
        $away = $data['away'] ?? [];
        $players = \is_array($away) ? ($away['players'] ?? []) : [];
        $players = \is_array($players) ? $players : [];

        return [
            'confirmed' => $data['confirmed'] ?? null,
            'formation' => \is_array($away) ? ($away['formation'] ?? null) : null,
            'player_colour' => \is_array($away) ? ($away['playerColor'] ?? null) : null,
            'goalkeeper_colour' => \is_array($away) ? ($away['goalkeeperColor'] ?? null) : null,
            'missing_players' => \is_array($away) ? ($away['missingPlayers'] ?? null) : null,
            'starters' => array_values(array_filter($players, static fn ($entry): bool => !(\is_array($entry) && (bool) ($entry['substitute'] ?? false)))),
            'substitutes' => array_values(array_filter($players, static fn ($entry): bool => \is_array($entry) && (bool) ($entry['substitute'] ?? false))),
        ];
    }

    /**
     * Shot map for the match, optionally scoped to a single team
     * (Python {@code shotmap}).
     *
     * @return array<string, mixed>
     */
    public function shotmap(?int $teamId = null): array
    {
        if (null !== $teamId) {
            /** @var array<string, mixed> $data */
            $data = $this->get("/event/{$this->matchId}/shotmap/{$teamId}");

            return $data;
        }

        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/shotmap");

        return $data;
    }

    /**
     * Heat map for a specific team in the match (Python {@code heatmap}).
     *
     * @return array<string, mixed>
     */
    public function heatmap(int $teamId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/heatmap/{$teamId}");

        return $data;
    }

    /**
     * Statistics for the match (Python {@code stats}).
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/statistics");

        return $data;
    }

    /**
     * Detailed information about the event (match) as a typed DTO (Python {@code get_match}).
     *
     * The primary match-detail getter. It cannot be named {@code get()} (that is
     * the protected request helper inherited from {@see AbstractEndpoint}, whose
     * {@code (string, array): array} signature PHP forbids overriding with a
     * DTO-returning one), so the event object is exposed here.
     */
    public function getMatch(): Event
    {
        return Event::fromArray($this->get("/event/{$this->matchId}"));
    }

    /**
     * Highlights for the match (Python {@code highlight}).
     *
     * @return array<string, mixed>
     */
    public function highlight(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/highlights");

        return $data;
    }

    /**
     * Commentary for the match (Python {@code commentary}).
     *
     * @return array<string, mixed>
     */
    public function commentary(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/comments");

        return $data;
    }

    /**
     * Team streaks for the match (Python {@code team_streaks}).
     *
     * @return array<string, mixed>
     */
    public function teamStreaks(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/team-streaks");

        return $data;
    }

    /**
     * Head-to-head results events for a specific match code (Python {@code h2h_results}).
     *
     * @return array<string, mixed>
     */
    public function h2hResults(string $matchCode): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$matchCode}/h2h/events");

        return $data;
    }

    /**
     * Win-probability graph for the match (Python {@code win_probability}).
     *
     * @return array<string, mixed>
     */
    public function winProbability(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/event/{$this->matchId}/graph/win-probability");

        return $data;
    }
}
