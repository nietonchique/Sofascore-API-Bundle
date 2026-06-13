<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A SofaScore sport (e.g. football, basketball).
 */
final readonly class Sport
{
    public function __construct(
        public ?int $id,
        public ?string $slug,
        public ?string $name,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Cast::int($data['id'] ?? null),
            Cast::string($data['slug'] ?? null),
            Cast::string($data['name'] ?? null),
        );
    }

    /**
     * @return array{id: int|null, slug: string|null, name: string|null}
     */
    public function toArray(): array
    {
        return ['id' => $this->id, 'slug' => $this->slug, 'name' => $this->name];
    }
}
