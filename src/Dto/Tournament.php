<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A SofaScore tournament / unique tournament (a "league"). Common fields are
 * typed; the complete payload remains available via {@see Tournament::$raw}.
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
        public ?int $userCount,
        public ?Sport $sport,
        public ?Country $category,
        public array $raw = [],
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $sport = Cast::array($data['sport'] ?? null);
        // SofaScore nests the country under "category".
        $category = Cast::array($data['category'] ?? null);

        return new self(
            Cast::int($data['id'] ?? null),
            Cast::string($data['name'] ?? null),
            Cast::string($data['slug'] ?? null),
            Cast::int($data['userCount'] ?? null),
            null !== $sport ? Sport::fromArray($sport) : null,
            null !== $category ? Country::fromArray($category) : null,
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
