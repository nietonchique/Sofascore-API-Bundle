<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Endpoint\Search;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Search::class)]
final class SearchTest extends TestCase
{
    private MockTransport $transport;

    private Search $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Search($this->transport, new Enums(), 'arsenal', 0);
    }

    public function testSearchAllBuildsPathAndQuery(): void
    {
        $this->transport->setResponse(['results' => []]);
        $result = $this->endpoint->searchAll();

        self::assertSame('/search/all/', $this->transport->lastEndpoint());
        self::assertSame(['q' => 'arsenal', 'page' => 0], $this->transport->lastQuery());
        self::assertSame(['results' => []], $result);
    }

    public function testSearchMatchBuildsPathAndQuery(): void
    {
        $this->endpoint->searchMatch();

        self::assertSame('/search/events/', $this->transport->lastEndpoint());
        self::assertSame(['q' => 'arsenal', 'page' => 0], $this->transport->lastQuery());
    }

    public function testSearchPlayersBuildsPathAndQuery(): void
    {
        $this->endpoint->searchPlayers();

        self::assertSame('/search/player-team-persons/', $this->transport->lastEndpoint());
        self::assertSame(['q' => 'arsenal', 'page' => 0], $this->transport->lastQuery());
    }

    public function testSearchTeamsBuildsPathAndQuery(): void
    {
        $this->endpoint->searchTeams();

        self::assertSame('/search/teams/', $this->transport->lastEndpoint());
        self::assertSame(['q' => 'arsenal', 'page' => 0], $this->transport->lastQuery());
    }

    public function testSearchLeaguesBuildsPathAndQuery(): void
    {
        $this->endpoint->searchLeagues();

        self::assertSame('/search/unique-tournaments/', $this->transport->lastEndpoint());
        self::assertSame(['q' => 'arsenal', 'page' => 0], $this->transport->lastQuery());
    }

    public function testPageIsForwardedInQuery(): void
    {
        $endpoint = new Search($this->transport, new Enums(), 'arsenal', 3);
        $endpoint->searchAll();

        self::assertSame(['q' => 'arsenal', 'page' => 3], $this->transport->lastQuery());
    }

    public function testSearchAllReturnsRawPayloadWithoutSport(): void
    {
        $payload = ['results' => [['type' => 'team', 'score' => 1.0]], 'meta' => 'x'];
        $this->transport->setResponse($payload);

        self::assertSame($payload, $this->endpoint->searchAll());
    }

    public function testSearchAllFiltersBySportAndReshapes(): void
    {
        $football = ['type' => 'team', 'entity' => ['sport' => ['id' => 1]]];
        $basketball = ['type' => 'team', 'entity' => ['sport' => ['id' => 2]]];
        $this->transport->setResponse(['results' => [$football, $basketball]]);

        $result = $this->endpoint->searchAll('football');

        self::assertSame('/search/all/', $this->transport->lastEndpoint());
        self::assertSame(['q' => 'arsenal', 'page' => 0], $this->transport->lastQuery());
        self::assertSame(['results' => [$football]], $result);
    }

    public function testSearchMatchFiltersByEventSport(): void
    {
        $football = ['type' => 'event', 'entity' => ['tournament' => ['category' => ['sport' => ['id' => 1]]]]];
        $basketball = ['type' => 'event', 'entity' => ['tournament' => ['category' => ['sport' => ['id' => 2]]]]];
        $this->transport->setResponse(['results' => [$football, $basketball]]);

        $result = $this->endpoint->searchMatch('football');

        self::assertSame(['results' => [$football]], $result);
    }

    public function testSearchPlayersFiltersByPlayerSport(): void
    {
        $football = ['type' => 'player', 'entity' => ['team' => ['sport' => ['id' => 1]]]];
        $basketball = ['type' => 'player', 'entity' => ['team' => ['sport' => ['id' => 2]]]];
        $this->transport->setResponse(['results' => [$football, $basketball]]);

        $result = $this->endpoint->searchPlayers('football');

        self::assertSame(['results' => [$football]], $result);
    }

    public function testSearchLeaguesFiltersByUniqueTournamentSport(): void
    {
        $football = ['type' => 'uniqueTournament', 'entity' => ['category' => ['sport' => ['id' => 1]]]];
        $basketball = ['type' => 'uniqueTournament', 'entity' => ['category' => ['sport' => ['id' => 2]]]];
        $this->transport->setResponse(['results' => [$football, $basketball]]);

        $result = $this->endpoint->searchLeagues('football');

        self::assertSame(['results' => [$football]], $result);
    }

    public function testGetSportIdResolvesEachEntryType(): void
    {
        self::assertSame(1, $this->endpoint->getSportId(
            ['type' => 'team', 'entity' => ['sport' => ['id' => 1]]],
        ));
        self::assertSame(1, $this->endpoint->getSportId(
            ['type' => 'player', 'entity' => ['team' => ['sport' => ['id' => 1]]]],
        ));
        self::assertSame(1, $this->endpoint->getSportId(
            ['type' => 'event', 'entity' => ['tournament' => ['category' => ['sport' => ['id' => 1]]]]],
        ));
        self::assertSame(1, $this->endpoint->getSportId(
            ['type' => 'uniqueTournament', 'entity' => ['category' => ['sport' => ['id' => 1]]]],
        ));
        self::assertNull($this->endpoint->getSportId(['type' => 'manager', 'entity' => []]));
    }

    public function testSearchAllRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->searchAll('quidditch');
    }

    public function testSearchMatchRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->searchMatch('quidditch');
    }

    public function testSearchPlayersRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->searchPlayers('quidditch');
    }

    public function testSearchTeamsRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->searchTeams('quidditch');
    }

    public function testSearchLeaguesRejectsInvalidSport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->endpoint->searchLeagues('quidditch');
    }
}
