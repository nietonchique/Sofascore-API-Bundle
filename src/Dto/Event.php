<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A SofaScore event (a match). Common fields are typed; the complete payload
 * remains available via {@see Event::$raw} / {@see Event::toArray()}.
 */
final readonly class Event
{
    /**
     * @param array<array-key, mixed> $raw
     */
    public function __construct(
        public ?int $id,
        public ?string $slug,
        public ?int $startTimestamp,
        public ?string $statusType,
        public ?int $winnerCode,
        public ?Tournament $tournament,
        public ?Team $homeTeam,
        public ?Team $awayTeam,
        public ?Score $homeScore,
        public ?Score $awayScore,
        public array $raw = [],
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // The detail endpoint wraps the event under an "event" key; accept both shapes.
        $event = Cast::array($data['event'] ?? null);
        if (null !== $event) {
            $data = $event;
        }

        $status = Cast::array($data['status'] ?? null) ?? [];
        $tournament = Cast::array($data['tournament'] ?? null);
        $homeTeam = Cast::array($data['homeTeam'] ?? null);
        $awayTeam = Cast::array($data['awayTeam'] ?? null);
        $homeScore = Cast::array($data['homeScore'] ?? null);
        $awayScore = Cast::array($data['awayScore'] ?? null);

        return new self(
            Cast::int($data['id'] ?? null),
            Cast::string($data['slug'] ?? null),
            Cast::int($data['startTimestamp'] ?? null),
            Cast::string($status['type'] ?? null),
            Cast::int($data['winnerCode'] ?? null),
            null !== $tournament ? Tournament::fromArray($tournament) : null,
            null !== $homeTeam ? Team::fromArray($homeTeam) : null,
            null !== $awayTeam ? Team::fromArray($awayTeam) : null,
            null !== $homeScore ? Score::fromArray($homeScore) : null,
            null !== $awayScore ? Score::fromArray($awayScore) : null,
            $data,
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array
    {
        return $this->raw;
    }
}
