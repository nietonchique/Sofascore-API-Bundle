<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * A team's score within an event (home/away score objects).
 */
final readonly class Score
{
    public function __construct(
        public ?int $current,
        public ?int $display,
        public ?int $period1,
        public ?int $period2,
        public ?int $normaltime,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Cast::int($data['current'] ?? null),
            Cast::int($data['display'] ?? null),
            Cast::int($data['period1'] ?? null),
            Cast::int($data['period2'] ?? null),
            Cast::int($data['normaltime'] ?? null),
        );
    }

    /**
     * @return array{current: int|null, display: int|null, period1: int|null, period2: int|null, normaltime: int|null}
     */
    public function toArray(): array
    {
        return [
            'current' => $this->current,
            'display' => $this->display,
            'period1' => $this->period1,
            'period2' => $this->period2,
            'normaltime' => $this->normaltime,
        ];
    }
}
