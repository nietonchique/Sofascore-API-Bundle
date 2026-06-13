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

        return new self(
            Cast::int($data['id'] ?? null),
            Cast::string($data['name'] ?? null),
            Cast::string($data['shortName'] ?? null),
            Cast::string($data['fullName'] ?? null),
            Cast::string($data['slug'] ?? null),
            Cast::string($data['nameCode'] ?? null),
            Cast::string($data['gender'] ?? null),
            Cast::int($data['type'] ?? null),
            Cast::bool($data['national'] ?? false),
            Cast::bool($data['disabled'] ?? false),
            Cast::int($data['userCount'] ?? null),
            null !== $sport ? Sport::fromArray($sport) : null,
            null !== $country ? Country::fromArray($country) : null,
            null !== $category ? Category::fromArray($category) : null,
            null !== $teamColors ? TeamColors::fromArray($teamColors) : null,
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
