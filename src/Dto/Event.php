<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A SofaScore event (a match). Common, stably-present fields are typed; the
 * complete payload remains available via {@see Event::$raw} / {@see Event::toArray()}.
 */
final readonly class Event
{
    /**
     * @param array<array-key, mixed> $raw
     */
    public function __construct(
        public ?int $id,
        public ?string $slug,
        public ?string $customId,
        public ?int $startTimestamp,
        public ?string $statusType,
        public ?int $statusCode,
        public ?string $statusDescription,
        public ?int $winnerCode,
        public ?Tournament $tournament,
        public ?Team $homeTeam,
        public ?Team $awayTeam,
        public ?Score $homeScore,
        public ?Score $awayScore,
        public ?FieldTranslations $fieldTranslations = null,
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
        $fieldTranslations = Cast::array($data['fieldTranslations'] ?? null);

        return new self(
            id: Cast::int($data['id'] ?? null),
            slug: Cast::string($data['slug'] ?? null),
            customId: Cast::string($data['customId'] ?? null),
            startTimestamp: Cast::int($data['startTimestamp'] ?? null),
            statusType: Cast::string($status['type'] ?? null),
            statusCode: Cast::int($status['code'] ?? null),
            statusDescription: Cast::string($status['description'] ?? null),
            winnerCode: Cast::int($data['winnerCode'] ?? null),
            tournament: null !== $tournament ? Tournament::fromArray($tournament) : null,
            homeTeam: null !== $homeTeam ? Team::fromArray($homeTeam) : null,
            awayTeam: null !== $awayTeam ? Team::fromArray($awayTeam) : null,
            homeScore: null !== $homeScore ? Score::fromArray($homeScore) : null,
            awayScore: null !== $awayScore ? Score::fromArray($awayScore) : null,
            fieldTranslations: null !== $fieldTranslations ? FieldTranslations::fromArray($fieldTranslations) : null,
            raw: $data,
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
