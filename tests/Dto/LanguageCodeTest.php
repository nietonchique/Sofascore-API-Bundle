<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Dto;

use Nietonchique\SofascoreApiBundle\Dto\LanguageCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LanguageCode::class)]
final class LanguageCodeTest extends TestCase
{
    public function testReturnsLabelForKnownCode(): void
    {
        self::assertSame('Russian', LanguageCode::label(LanguageCode::RU));
        self::assertSame('Serbian', LanguageCode::label(LanguageCode::SR));
        self::assertSame('English', LanguageCode::label(LanguageCode::EN));
    }

    public function testReturnsCodeAsLabelForUnknownCode(): void
    {
        self::assertSame('xx', LanguageCode::label('xx'));
    }
}
