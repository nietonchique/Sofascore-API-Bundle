<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * Type-narrowing helpers for turning decoded-JSON {@code mixed} values into
 * typed DTO fields without unsafe casts. Returns {@code null} for absent or
 * incompatible values, which is exactly what the tolerant DTO factories want.
 *
 * @internal
 */
final class Cast
{
    public static function int(mixed $value): ?int
    {
        if (\is_int($value)) {
            return $value;
        }
        if (\is_float($value)) {
            return (int) $value;
        }
        if (\is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    public static function string(mixed $value): ?string
    {
        if (\is_string($value)) {
            return $value;
        }
        if (\is_int($value) || \is_float($value)) {
            return (string) $value;
        }

        return null;
    }

    public static function bool(mixed $value): bool
    {
        return true === $value || 1 === $value || '1' === $value;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    public static function array(mixed $value): ?array
    {
        return \is_array($value) ? $value : null;
    }

    /**
     * @return array<string, string>
     */
    public static function stringMap(mixed $value): array
    {
        $array = self::array($value);
        if (null === $array) {
            return [];
        }

        $result = [];
        foreach ($array as $key => $item) {
            $string = self::string($item);
            if (null !== $string && \is_string($key)) {
                $result[$key] = $string;
            }
        }

        return $result;
    }
}
