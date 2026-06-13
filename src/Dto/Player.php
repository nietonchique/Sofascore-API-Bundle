<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A SofaScore player. Common, stably-present fields are typed; the complete
 * payload remains available via {@see Player::$raw} / {@see Player::toArray()}.
 */
final readonly class Player
{
    /**
     * @param array<array-key, mixed> $raw
     */
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $shortName,
        public ?string $slug,
        public ?string $position,
        public ?string $jerseyNumber,
        public ?int $shirtNumber,
        public ?int $height,
        public ?string $dateOfBirth,
        public ?int $dateOfBirthTimestamp,
        public ?int $contractUntilTimestamp,
        public ?string $gender,
        public bool $deceased,
        public ?int $userCount,
        public ?Country $country,
        public ?Team $team,
        public array $raw = [],
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $country = Cast::array($data['country'] ?? null);
        $team = Cast::array($data['team'] ?? null);

        return new self(
            Cast::int($data['id'] ?? null),
            Cast::string($data['name'] ?? null),
            Cast::string($data['shortName'] ?? null),
            Cast::string($data['slug'] ?? null),
            Cast::string($data['position'] ?? null),
            // SofaScore sends jerseyNumber as a string (e.g. "7"); shirtNumber is the int.
            Cast::string($data['jerseyNumber'] ?? null),
            Cast::int($data['shirtNumber'] ?? null),
            Cast::int($data['height'] ?? null),
            Cast::string($data['dateOfBirth'] ?? null),
            Cast::int($data['dateOfBirthTimestamp'] ?? null),
            Cast::int($data['contractUntilTimestamp'] ?? null),
            Cast::string($data['gender'] ?? null),
            Cast::bool($data['deceased'] ?? false),
            Cast::int($data['userCount'] ?? null),
            null !== $country ? Country::fromArray($country) : null,
            null !== $team ? Team::fromArray($team) : null,
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
