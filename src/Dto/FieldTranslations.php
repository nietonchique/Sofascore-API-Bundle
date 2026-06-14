<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * SofaScore embedded translations for a single named entity.
 *
 * Payload shape:
 *   "fieldTranslations": {
 *     "nameTranslation": {"sr": "...", "ru": "..."},
 *     "shortNameTranslation": {}
 *   }
 */
final readonly class FieldTranslations
{
    /**
     * @param array<string, string> $name
     * @param array<string, string> $shortName
     */
    public function __construct(
        public array $name = [],
        public array $shortName = [],
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $name = Cast::array($data['nameTranslation'] ?? null);
        $shortName = Cast::array($data['shortNameTranslation'] ?? null);

        return new self(
            null !== $name ? Cast::stringMap($name) : [],
            null !== $shortName ? Cast::stringMap($shortName) : [],
        );
    }

    public function nameIn(string $locale, ?string $default = null): ?string
    {
        return $this->name[$locale] ?? $default;
    }

    public function shortNameIn(string $locale, ?string $default = null): ?string
    {
        return $this->shortName[$locale] ?? $default;
    }

    /**
     * @return array{nameTranslation: array<string, string>, shortNameTranslation: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'nameTranslation' => $this->name,
            'shortNameTranslation' => $this->shortName,
        ];
    }
}
