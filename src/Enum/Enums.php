<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Enum;

use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;

/**
 * Typed accessor over the bundled {@code enums.json} (ported from the Python
 * wrapper's {@code tools/enums.json}). Centralises the sport-slug normalisation
 * and validation that several endpoint methods perform.
 */
final class Enums
{
    /**
     * @var array<string, int>
     */
    private readonly array $sports;

    public function __construct(?string $dataFile = null)
    {
        $file = $dataFile ?? \dirname(__DIR__).'/Resources/data/enums.json';
        $contents = @file_get_contents($file);
        if (false === $contents) {
            throw new InvalidArgumentException(\sprintf('Could not read enums data file "%s".', $file));
        }

        /** @var array{sports?: array<string, int>} $data */
        $data = json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
        $this->sports = $data['sports'] ?? [];
    }

    /**
     * @return array<string, int>
     */
    public function sports(): array
    {
        return $this->sports;
    }

    /**
     * Lower-case and dash-normalise a sport name, mirroring the Python
     * {@code sport.lower().replace(' ', '-')}.
     */
    public function normalizeSport(string $sport): string
    {
        return str_replace(' ', '-', strtolower($sport));
    }

    public function hasSport(string $sport): bool
    {
        return \array_key_exists($this->normalizeSport($sport), $this->sports);
    }

    /**
     * Validate a sport name and return its normalised slug.
     *
     * @throws InvalidArgumentException when the sport is unknown
     */
    public function assertSport(string $sport): string
    {
        $slug = $this->normalizeSport($sport);
        if (!\array_key_exists($slug, $this->sports)) {
            throw new InvalidArgumentException(\sprintf('Invalid sport "%s". Valid sports: %s.', $sport, implode(', ', array_keys($this->sports))));
        }

        return $slug;
    }

    public function sportId(string $sport): int
    {
        return $this->sports[$this->assertSport($sport)];
    }
}
