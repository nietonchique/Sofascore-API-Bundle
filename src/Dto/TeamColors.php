<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A team's kit colours (`teamColors` object).
 */
final readonly class TeamColors
{
    public function __construct(
        public ?string $primary,
        public ?string $secondary,
        public ?string $text,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Cast::string($data['primary'] ?? null),
            Cast::string($data['secondary'] ?? null),
            Cast::string($data['text'] ?? null),
        );
    }

    /**
     * @return array{primary: string|null, secondary: string|null, text: string|null}
     */
    public function toArray(): array
    {
        return ['primary' => $this->primary, 'secondary' => $this->secondary, 'text' => $this->text];
    }
}
