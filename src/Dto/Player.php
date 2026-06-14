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
        public ?FieldTranslations $fieldTranslations = null,
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
        $fieldTranslations = Cast::array($data['fieldTranslations'] ?? null);

        return new self(
            id: Cast::int($data['id'] ?? null),
            name: Cast::string($data['name'] ?? null),
            shortName: Cast::string($data['shortName'] ?? null),
            slug: Cast::string($data['slug'] ?? null),
            position: Cast::string($data['position'] ?? null),
            // SofaScore sends jerseyNumber as a string (e.g. "7"); shirtNumber is the int.
            jerseyNumber: Cast::string($data['jerseyNumber'] ?? null),
            shirtNumber: Cast::int($data['shirtNumber'] ?? null),
            height: Cast::int($data['height'] ?? null),
            dateOfBirth: Cast::string($data['dateOfBirth'] ?? null),
            dateOfBirthTimestamp: Cast::int($data['dateOfBirthTimestamp'] ?? null),
            contractUntilTimestamp: Cast::int($data['contractUntilTimestamp'] ?? null),
            gender: Cast::string($data['gender'] ?? null),
            deceased: Cast::bool($data['deceased'] ?? false),
            userCount: Cast::int($data['userCount'] ?? null),
            country: null !== $country ? Country::fromArray($country) : null,
            team: null !== $team ? Team::fromArray($team) : null,
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
