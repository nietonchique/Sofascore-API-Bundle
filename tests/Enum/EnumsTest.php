<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Enum;

use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Enums::class)]
final class EnumsTest extends TestCase
{
    public function testLoadsBundledSports(): void
    {
        $enums = new Enums();

        self::assertSame(1, $enums->sportId('football'));
        self::assertSame(2, $enums->sportId('basketball'));
        self::assertArrayHasKey('ice-hockey', $enums->sports());
    }

    #[DataProvider('normalizationCases')]
    public function testNormalizeSport(string $input, string $expected): void
    {
        self::assertSame($expected, (new Enums())->normalizeSport($input));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function normalizationCases(): iterable
    {
        yield 'lowercase' => ['Football', 'football'];
        yield 'spaces to dashes' => ['Ice Hockey', 'ice-hockey'];
        yield 'american football' => ['American Football', 'american-football'];
    }

    public function testAssertSportReturnsSlug(): void
    {
        self::assertSame('ice-hockey', (new Enums())->assertSport('Ice Hockey'));
    }

    public function testAssertSportThrowsOnUnknown(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new Enums())->assertSport('quidditch');
    }

    public function testHasSport(): void
    {
        $enums = new Enums();

        self::assertTrue($enums->hasSport('Football'));
        self::assertFalse($enums->hasSport('quidditch'));
    }
}
