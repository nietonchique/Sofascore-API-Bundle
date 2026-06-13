<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle;

use Nietonchique\SofascoreApiBundle\Endpoint\AmericanFootball;
use Nietonchique\SofascoreApiBundle\Endpoint\Baseball;
use Nietonchique\SofascoreApiBundle\Endpoint\Basketball;
use Nietonchique\SofascoreApiBundle\Endpoint\Cricket;
use Nietonchique\SofascoreApiBundle\Endpoint\Esports;
use Nietonchique\SofascoreApiBundle\Endpoint\Flag;
use Nietonchique\SofascoreApiBundle\Endpoint\IceHockey;
use Nietonchique\SofascoreApiBundle\Endpoint\League;
use Nietonchique\SofascoreApiBundle\Endpoint\Manager;
use Nietonchique\SofascoreApiBundle\Endpoint\MatchEndpoint;
use Nietonchique\SofascoreApiBundle\Endpoint\Mma;
use Nietonchique\SofascoreApiBundle\Endpoint\Motorsport;
use Nietonchique\SofascoreApiBundle\Endpoint\News;
use Nietonchique\SofascoreApiBundle\Endpoint\Player;
use Nietonchique\SofascoreApiBundle\Endpoint\PlayerSearch;
use Nietonchique\SofascoreApiBundle\Endpoint\Rugby;
use Nietonchique\SofascoreApiBundle\Endpoint\Search;
use Nietonchique\SofascoreApiBundle\Endpoint\Team;
use Nietonchique\SofascoreApiBundle\Endpoint\Tennis;
use Nietonchique\SofascoreApiBundle\Endpoint\Transfers;
use Nietonchique\SofascoreApiBundle\Endpoint\UserData;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Transport\HttpClientTransport;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Entry point of the bundle. Wraps a {@see TransportInterface} and provides a
 * factory method per endpoint group, mirroring how the Python wrapper builds its
 * classes from the shared API object.
 *
 * Usable standalone (without Symfony's container):
 *
 *     $client = SofascoreClient::create();
 *     $results = $client->search('arsenal')->searchAll();
 */
final class SofascoreClient
{
    private readonly Enums $enums;

    public function __construct(
        private readonly TransportInterface $transport,
        ?Enums $enums = null,
    ) {
        $this->enums = $enums ?? new Enums();
    }

    /**
     * Convenience factory for standalone (non-DI) usage: builds a client on top
     * of the default HTTP transport.
     */
    public static function create(): self
    {
        return new self(new HttpClientTransport(HttpClient::create()));
    }

    public function transport(): TransportInterface
    {
        return $this->transport;
    }

    public function search(string $searchString, int $page = 0): Search
    {
        return new Search($this->transport, $this->enums, $searchString, $page);
    }

    public function match(?int $matchId = null): MatchEndpoint
    {
        return new MatchEndpoint($this->transport, $this->enums, $matchId);
    }

    public function playerSearch(string $query): PlayerSearch
    {
        return new PlayerSearch($this->transport, $this->enums, $query);
    }

    public function player(int $playerId): Player
    {
        return new Player($this->transport, $this->enums, $playerId);
    }

    public function team(int $teamId): Team
    {
        return new Team($this->transport, $this->enums, $teamId);
    }

    public function league(int $leagueId): League
    {
        return new League($this->transport, $this->enums, $leagueId);
    }

    public function manager(int $managerId): Manager
    {
        return new Manager($this->transport, $this->enums, $managerId);
    }

    public function flag(string $flagCode): Flag
    {
        return new Flag($this->transport, $this->enums, $flagCode);
    }

    public function transfers(): Transfers
    {
        return new Transfers($this->transport, $this->enums);
    }

    public function news(): News
    {
        return new News($this->transport, $this->enums);
    }

    public function userData(): UserData
    {
        return new UserData($this->transport, $this->enums);
    }

    public function americanFootball(): AmericanFootball
    {
        return new AmericanFootball($this->transport, $this->enums);
    }

    public function baseball(): Baseball
    {
        return new Baseball($this->transport, $this->enums);
    }

    public function basketball(): Basketball
    {
        return new Basketball($this->transport, $this->enums);
    }

    public function cricket(): Cricket
    {
        return new Cricket($this->transport, $this->enums);
    }

    public function esports(): Esports
    {
        return new Esports($this->transport, $this->enums);
    }

    public function iceHockey(): IceHockey
    {
        return new IceHockey($this->transport, $this->enums);
    }

    public function mma(): Mma
    {
        return new Mma($this->transport, $this->enums);
    }

    public function motorsport(): Motorsport
    {
        return new Motorsport($this->transport, $this->enums);
    }

    public function rugby(): Rugby
    {
        return new Rugby($this->transport, $this->enums);
    }

    public function tennis(): Tennis
    {
        return new Tennis($this->transport, $this->enums);
    }
}
