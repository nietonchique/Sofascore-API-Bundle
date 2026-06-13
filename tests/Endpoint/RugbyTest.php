<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use DateTimeImmutable;
use Nietonchique\SofascoreApiBundle\Endpoint\Rugby;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Rugby::class)]
final class RugbyTest extends TestCase
{
    private MockTransport $transport;

    private Rugby $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Rugby($this->transport, new Enums());
    }

    public function testTotalMatchesExtractsRugbyKey(): void
    {
        $this->transport->setResponse([
            'football' => ['live' => 5, 'total' => 100],
            'rugby' => ['live' => 8, 'total' => 16],
        ]);
        $result = $this->endpoint->totalMatches();

        self::assertSame('/sport/0/event-count', $this->transport->lastEndpoint());
        self::assertSame(['live' => 8, 'total' => 16], $result);
    }

    public function testTotalMatchesDefaultsToEmptyArrayWhenMissing(): void
    {
        $this->transport->setResponse(['football' => ['live' => 5, 'total' => 100]]);

        self::assertSame([], $this->endpoint->totalMatches());
    }

    public function testAllTournamentsDefaultsCountry(): void
    {
        $this->endpoint->allTournaments();

        self::assertSame('/config/default-unique-tournaments/GB/rugby', $this->transport->lastEndpoint());
    }

    public function testAllTournamentsUppercasesCountry(): void
    {
        $this->endpoint->allTournaments('fr');

        self::assertSame('/config/default-unique-tournaments/FR/rugby', $this->transport->lastEndpoint());
    }

    public function testCategories(): void
    {
        $this->endpoint->categories();

        self::assertSame('/sport/rugby/categories', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateWithExplicitDateAndSport(): void
    {
        $this->endpoint->matchesByDate('ice-hockey', '2025-01-31');

        self::assertSame('/sport/ice-hockey/scheduled-events/2025-01-31', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateDefaultsSportAndDate(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->endpoint->matchesByDate();

        self::assertSame("/sport/rugby/scheduled-events/{$today}", $this->transport->lastEndpoint());
    }

    public function testMatchesByDateNormalisesSportSlug(): void
    {
        $this->endpoint->matchesByDate('Ice Hockey', '2025-01-31');

        self::assertSame('/sport/ice-hockey/scheduled-events/2025-01-31', $this->transport->lastEndpoint());
    }

    public function testMatchesByDateRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->matchesByDate('quidditch');
    }

    public function testSeasonGames(): void
    {
        $this->endpoint->seasonGames(132, 65360);

        self::assertSame('/unique-tournament/132/season/65360/team-events/total', $this->transport->lastEndpoint());
    }
}
