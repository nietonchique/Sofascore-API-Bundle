<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A SofaScore team. Common, stably-present fields are typed; the complete payload
 * remains available via {@see Team::$raw} / {@see Team::toArray()}.
 */
final readonly class Team
{
    /**
     * @param array<array-key, mixed> $raw
     */
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $shortName,
        public ?string $fullName,
        public ?string $slug,
        public ?string $nameCode,
        public ?string $gender,
        public ?int $type,
        public bool $national,
        public bool $disabled,
        public ?int $userCount,
        public ?Sport $sport,
        public ?Country $country,
        public ?Category $category,
        public ?TeamColors $teamColors,
        public ?FieldTranslations $fieldTranslations = null,
        public array $raw = [],
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $sport = Cast::array($data['sport'] ?? null);
        $country = Cast::array($data['country'] ?? null);
        $category = Cast::array($data['category'] ?? null);
        $teamColors = Cast::array($data['teamColors'] ?? null);
        $fieldTranslations = Cast::array($data['fieldTranslations'] ?? null);

        return new self(
            id: Cast::int($data['id'] ?? null),
            name: Cast::string($data['name'] ?? null),
            shortName: Cast::string($data['shortName'] ?? null),
            fullName: Cast::string($data['fullName'] ?? null),
            slug: Cast::string($data['slug'] ?? null),
            nameCode: Cast::string($data['nameCode'] ?? null),
            gender: Cast::string($data['gender'] ?? null),
            type: Cast::int($data['type'] ?? null),
            national: Cast::bool($data['national'] ?? false),
            disabled: Cast::bool($data['disabled'] ?? false),
            userCount: Cast::int($data['userCount'] ?? null),
            sport: null !== $sport ? Sport::fromArray($sport) : null,
            country: null !== $country ? Country::fromArray($country) : null,
            category: null !== $category ? Category::fromArray($category) : null,
            teamColors: null !== $teamColors ? TeamColors::fromArray($teamColors) : null,
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
