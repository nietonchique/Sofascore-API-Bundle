<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A SofaScore team. Common fields are typed; the complete payload remains
 * available via {@see Team::$raw} / {@see Team::toArray()}.
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
        public ?string $slug,
        public ?string $nameCode,
        public bool $national,
        public ?int $userCount,
        public ?Sport $sport,
        public ?Country $country,
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

        return new self(
            Cast::int($data['id'] ?? null),
            Cast::string($data['name'] ?? null),
            Cast::string($data['shortName'] ?? null),
            Cast::string($data['slug'] ?? null),
            Cast::string($data['nameCode'] ?? null),
            Cast::bool($data['national'] ?? false),
            Cast::int($data['userCount'] ?? null),
            null !== $sport ? Sport::fromArray($sport) : null,
            null !== $country ? Country::fromArray($country) : null,
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
