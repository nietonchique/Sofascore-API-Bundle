<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * Base class for every endpoint group. Holds the transport and the enum helper
 * and exposes small request helpers so the concrete classes read like a thin,
 * faithful translation of the Python wrapper.
 */
abstract class AbstractEndpoint
{
    public function __construct(
        protected readonly TransportInterface $transport,
        protected readonly Enums $enums,
    ) {
    }

    /**
     * @param array<string, scalar|null> $query
     *
     * @return array<array-key, mixed>
     */
    protected function get(string $endpoint, array $query = []): array
    {
        return $this->transport->get($endpoint, $query);
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getRaw(string $url): array
    {
        return $this->transport->getRaw($url);
    }

    /**
     * Current SofaScore calendar endpoint for tournaments scheduled on a date.
     *
     * @return array<string, mixed>
     */
    protected function getScheduledTournamentsByDate(string $sport, ?string $date = null, int $page = 1): array
    {
        if ($page < 1) {
            throw new InvalidArgumentException('Page must be greater than or equal to 1.');
        }

        $date ??= $this->today();
        $sport = $this->enums->assertSport($sport);

        /** @var array<string, mixed> $data */
        $data = $this->get("/sport/{$sport}/scheduled-tournaments/{$date}/page/{$page}");

        return $data;
    }

    /**
     * Compatibility envelope for the old "by date" methods. SofaScore no longer
     * serves /sport/{sport}/scheduled-events/{date}; the current calendar first
     * returns scheduled tournament buckets and then per-tournament event lists.
     *
     * @return array{events: list<array<string, mixed>>}
     */
    protected function getScheduledEventsByDate(string $sport, ?string $date = null): array
    {
        $date ??= $this->today();
        $sport = $this->enums->assertSport($sport);

        $events = [];
        $seenEvents = [];
        $seenTournamentEndpoints = [];

        for ($page = 1;; ++$page) {
            $calendar = $this->getScheduledTournamentsByDate($sport, $date, $page);
            foreach ($this->listOfArrays($calendar['scheduled'] ?? null) as $scheduled) {
                $this->appendEvents($scheduled, $events, $seenEvents);

                $endpoint = $this->scheduledTournamentEventsEndpoint($scheduled, $date);
                if (null === $endpoint || isset($seenTournamentEndpoints[$endpoint])) {
                    continue;
                }

                $seenTournamentEndpoints[$endpoint] = true;
                /** @var array<string, mixed> $details */
                $details = $this->get($endpoint);
                $this->appendEvents($details, $events, $seenEvents);
            }

            if (true !== ($calendar['hasNextPage'] ?? false)) {
                break;
            }
        }

        return ['events' => $events];
    }

    /**
     * Current date as {@code Y-m-d}, used as a default for "by date" endpoints
     * (mirrors the Python {@code datetime.datetime.now().strftime('%Y-%m-%d')}).
     */
    protected function today(): string
    {
        return (new DateTimeImmutable())->format('Y-m-d');
    }

    /**
     * @param array<string, mixed>       $source
     * @param list<array<string, mixed>> $events
     * @param array<string, true>        $seenEvents
     */
    private function appendEvents(array $source, array &$events, array &$seenEvents): void
    {
        foreach ($this->listOfArrays($source['events'] ?? null) as $event) {
            $key = $this->eventKey($event);
            if (isset($seenEvents[$key])) {
                continue;
            }

            $seenEvents[$key] = true;
            $events[] = $event;
        }
    }

    /**
     * @param array<string, mixed> $scheduled
     */
    private function scheduledTournamentEventsEndpoint(array $scheduled, string $date): ?string
    {
        $tournament = $this->arrayValue($scheduled['tournament'] ?? null);
        if ([] === $tournament) {
            return null;
        }

        $uniqueTournament = $this->arrayValue($tournament['uniqueTournament'] ?? null);
        $uniqueTournamentId = $this->intValue($uniqueTournament['id'] ?? null);
        if (null !== $uniqueTournamentId) {
            return "/unique-tournament/{$uniqueTournamentId}/scheduled-events/{$date}";
        }

        $tournamentId = $this->intValue($tournament['id'] ?? null);
        if (null !== $tournamentId) {
            return "/tournament/{$tournamentId}/scheduled-events/{$date}";
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function listOfArrays(mixed $value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            if (\is_array($item)) {
                $items[] = $this->stringKeyedArray($item);
            }
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function arrayValue(mixed $value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        return $this->stringKeyedArray($value);
    }

    /**
     * @param array<array-key, mixed> $value
     *
     * @return array<string, mixed>
     */
    private function stringKeyedArray(array $value): array
    {
        $result = [];
        foreach ($value as $key => $item) {
            if (\is_string($key)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $event
     */
    private function eventKey(array $event): string
    {
        $id = $this->intValue($event['id'] ?? null);
        if (null !== $id) {
            return 'id:'.$id;
        }

        return 'hash:'.hash('xxh128', serialize($event));
    }

    private function intValue(mixed $value): ?int
    {
        return \is_int($value) ? $value : null;
    }
}
