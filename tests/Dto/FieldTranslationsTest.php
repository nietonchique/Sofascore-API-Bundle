<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Dto;

use Nietonchique\SofascoreApiBundle\Dto\FieldTranslations;
use Nietonchique\SofascoreApiBundle\Dto\LanguageCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FieldTranslations::class)]
final class FieldTranslationsTest extends TestCase
{
    public function testParsesNameAndShortNameTranslations(): void
    {
        $translations = FieldTranslations::fromArray([
            'nameTranslation' => ['ru' => 'Арсенал', 'sr' => 'Арсенал'],
            'shortNameTranslation' => ['ru' => 'АРС'],
        ]);

        self::assertSame(['ru' => 'Арсенал', 'sr' => 'Арсенал'], $translations->name);
        self::assertSame(['ru' => 'АРС'], $translations->shortName);
    }

    public function testReturnsTranslationByLocale(): void
    {
        $translations = FieldTranslations::fromArray([
            'nameTranslation' => ['ru' => 'Арсенал'],
        ]);

        self::assertSame('Арсенал', $translations->nameIn(LanguageCode::RU));
        self::assertNull($translations->nameIn(LanguageCode::SR));
        self::assertSame('fallback', $translations->nameIn(LanguageCode::SR, 'fallback'));
    }

    public function testToleratesEmptyTranslations(): void
    {
        self::assertSame([], FieldTranslations::fromArray([])->name);
        self::assertSame([], FieldTranslations::fromArray(['nameTranslation' => []])->name);
        self::assertSame([], FieldTranslations::fromArray(['shortNameTranslation' => []])->shortName);
    }

    public function testIgnoresNonStringValues(): void
    {
        $translations = FieldTranslations::fromArray([
            'nameTranslation' => ['ru' => 'Арсенал', 'bad' => 123, 'worse' => null],
        ]);

        self::assertSame(['ru' => 'Арсенал', 'bad' => '123'], $translations->name);
    }

    public function testSerializesToArray(): void
    {
        $translations = new FieldTranslations(['ru' => 'Арсенал'], ['ru' => 'АРС']);

        self::assertSame([
            'nameTranslation' => ['ru' => 'Арсенал'],
            'shortNameTranslation' => ['ru' => 'АРС'],
        ], $translations->toArray());
    }
}
