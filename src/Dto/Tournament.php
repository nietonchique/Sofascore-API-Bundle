<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A SofaScore unique tournament (a "league"). Common, stably-present fields are
 * typed; the complete payload remains available via {@see Tournament::$raw}.
 *
 * Note: SofaScore does not expose `sport` at the top level of a unique
 * tournament — it lives under `category.sport`, which is where {@see $sport} is
 * resolved from (falling back to a top-level `sport` if one is ever present).
 */
final readonly class Tournament
{
    /**
     * @param array<array-key, mixed> $raw
     */
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $slug,
        public ?string $gender,
        public ?int $userCount,
        public ?string $primaryColorHex,
        public ?string $secondaryColorHex,
        public ?Category $category,
        public ?Sport $sport,
        public array $raw = [],
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $category = Cast::array($data['category'] ?? null);
        $sport = Cast::array($data['sport'] ?? null);
        if (null === $sport && null !== $category) {
            $sport = Cast::array($category['sport'] ?? null);
        }

        return new self(
            Cast::int($data['id'] ?? null),
            Cast::string($data['name'] ?? null),
            Cast::string($data['slug'] ?? null),
            Cast::string($data['gender'] ?? null),
            Cast::int($data['userCount'] ?? null),
            Cast::string($data['primaryColorHex'] ?? null),
            Cast::string($data['secondaryColorHex'] ?? null),
            null !== $category ? Category::fromArray($category) : null,
            null !== $sport ? Sport::fromArray($sport) : null,
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
