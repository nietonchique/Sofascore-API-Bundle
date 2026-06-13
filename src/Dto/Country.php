<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A country reference as embedded in teams, players and tournaments.
 */
final readonly class Country
{
    public function __construct(
        public ?string $alpha2,
        public ?string $alpha3,
        public ?string $name,
        public ?string $slug,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Cast::string($data['alpha2'] ?? null),
            Cast::string($data['alpha3'] ?? null),
            Cast::string($data['name'] ?? null),
            Cast::string($data['slug'] ?? null),
        );
    }

    /**
     * @return array{alpha2: string|null, alpha3: string|null, name: string|null, slug: string|null}
     */
    public function toArray(): array
    {
        return [
            'alpha2' => $this->alpha2,
            'alpha3' => $this->alpha3,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
