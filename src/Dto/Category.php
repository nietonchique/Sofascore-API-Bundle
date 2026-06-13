<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A SofaScore category (the country/region grouping a tournament or team belongs
 * to). Carries its own embedded sport and country. Common fields are typed; the
 * full payload remains available via {@see Category::$raw}.
 */
final readonly class Category
{
    /**
     * @param array<array-key, mixed> $raw
     */
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $slug,
        public ?string $flag,
        public ?string $alpha2,
        public ?int $priority,
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
            Cast::string($data['slug'] ?? null),
            Cast::string($data['flag'] ?? null),
            Cast::string($data['alpha2'] ?? null),
            Cast::int($data['priority'] ?? null),
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
