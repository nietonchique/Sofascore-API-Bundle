<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Endpoint\Flag;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Flag::class)]
final class FlagTest extends TestCase
{
    private MockTransport $transport;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
    }

    public function testImageBuildsLowercasedUrlWithoutApiCall(): void
    {
        $endpoint = new Flag($this->transport, new Enums(), 'ENG');

        self::assertSame(
            'https://www.sofascore.com/static/images/flags/eng.png',
            $endpoint->image(),
        );
        self::assertSame(0, $this->transport->callCount());
    }

    public function testImageWithAlreadyLowercaseCode(): void
    {
        $endpoint = new Flag($this->transport, new Enums(), 'gb');

        self::assertSame(
            'https://www.sofascore.com/static/images/flags/gb.png',
            $endpoint->image(),
        );
        self::assertSame(0, $this->transport->callCount());
    }
}
