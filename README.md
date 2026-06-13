# SofaScore API Bundle

A Symfony bundle **and** standalone PHP client for the (unofficial) [SofaScore](https://www.sofascore.com)
API — matches, players, teams, tournaments and live per-sport statistics.

It is a faithful PHP port of the Python [`sofascore-wrapper`](https://github.com/tommhe14/sofascore-wrapper)
library, redesigned around a pluggable transport layer, typed DTOs for the core
entities, and full PHPStan (max) / Deptrac / PHPUnit coverage.

> **Disclaimer.** This is an **unofficial** client and is not affiliated with,
> endorsed by, or supported by SofaScore. The API it talks to is undocumented and
> may change or block access at any time. Using it may violate SofaScore's Terms
> of Service — use at your own risk.

## Requirements

- PHP **8.2+**
- Symfony **7.1+** components (when used as a bundle)
- Optional: a Chromium/Chrome binary + [`chrome-php/chrome`](https://github.com/chrome-php/chrome)
  for the headless-browser transport (Cloudflare fallback)

## Installation

```bash
composer require nietonchique/sofascore-api-bundle
```

In a Symfony application using Symfony Flex the bundle is registered
automatically. Otherwise add it to `config/bundles.php`:

```php
return [
    // ...
    Nietonchique\SofascoreApiBundle\SofascoreApiBundle::class => ['all' => true],
];
```

## Usage

### Standalone (any PHP project)

```php
use Nietonchique\SofascoreApiBundle\SofascoreClient;

$client = SofascoreClient::create(); // default HTTP transport

$results = $client->search('arsenal')->searchAll();
$event   = $client->match(12436870)->getMatch();   // Dto\Event
$h2h     = $client->match(12436870)->h2h();         // array
$team    = $client->team(42)->getTeam();            // Dto\Team
$games   = $client->basketball()->gamesByDate('basketball', '2026-01-15');
```

### In Symfony (dependency injection)

`SofascoreClient` and every endpoint group are autowired services:

```php
use Nietonchique\SofascoreApiBundle\SofascoreClient;

final class ScoresController
{
    public function __construct(private readonly SofascoreClient $sofascore)
    {
    }

    public function live(): array
    {
        return $this->sofascore->match()->liveGames();
    }
}
```

## Endpoint groups

Access each group via the client factory methods:

| Method | Group | Bound argument |
|---|---|---|
| `search(string $q, int $page = 0)` | `Search` | search term + page |
| `match(?int $matchId = null)` | `MatchEndpoint` | match (event) id |
| `player(int $playerId)` | `Player` | player id |
| `playerSearch(string $query)` | `PlayerSearch` | query |
| `team(int $teamId)` | `Team` | team id |
| `league(int $leagueId)` | `League` | unique-tournament id |
| `manager(int $managerId)` | `Manager` | manager id |
| `transfers()` | `Transfers` | — |
| `news()` | `News` | — |
| `userData()` | `UserData` | — |
| `flag(string $flagCode)` | `Flag` | country code |
| `americanFootball()` / `baseball()` / `basketball()` / `cricket()` / `esports()` / `iceHockey()` / `mma()` / `motorsport()` / `rugby()` / `tennis()` | per-sport groups | — |

### Return types

The API surface is large and SofaScore changes response fields without notice, so
the return style is intentionally hybrid and predictable:

- The **primary entity-detail getters** return typed DTOs:
  `MatchEndpoint::getMatch(): Event`, `Player::getPlayer(): Player`,
  `Team::getTeam(): Team`, `League::getLeague(): Tournament`. Every DTO keeps the
  full original payload accessible via `->raw` / `->toArray()`.
- **Every other method** returns a decoded `array` (the raw JSON), exactly as the
  Python library does.

## Transports & Cloudflare (403)

SofaScore sits behind Cloudflare. The bundle ships three transports behind a
single `TransportInterface`:

- **`http`** — `HttpClientTransport`, a plain Symfony HTTP client with a realistic
  browser header set. Fast, no external dependencies.
- **`chrome`** — `ChromeTransport`, drives a headless Chromium via
  `chrome-php/chrome` (no Node.js). Warms up on the site root to obtain a
  Cloudflare clearance cookie before calling the API.
- **`chain`** (default) — tries HTTP first and falls back to Chrome on a 403.

> **The `X-Requested-With` header is required.** SofaScore answers every API
> request that lacks it with a Cloudflare `403 "challenge"`. `HttpClientTransport`
> sends it automatically (the value is not validated — a random per-instance hex
> token is used to mimic the site's own XHR token), so the default `http`
> transport reaches the API directly, no browser needed.
>
> **Some IPs are geo/reputation-blocked** (e.g. requests originating from Russia,
> or certain datacenter ranges) and get a `403` regardless of headers. Route
> through a clean exit with the `http.proxy` option — any SOCKS5/HTTP proxy works:
>
> ```yaml
> sofascore_api:
>     http:
>         proxy: 'socks5h://127.0.0.1:1080'
> ```
>
> When still blocked, the transport raises `ApiBlockedException` rather than
> returning bogus data, and `ChainTransport` falls back to the Chrome transport.

### Configuration (bundle)

All decorators are **opt-in and disabled by default**:

```yaml
# config/packages/sofascore_api.yaml
sofascore_api:
    transport: chain          # http | chrome | chain
    http:
        timeout: 10.0
        proxy: null
    chrome:
        binary: google-chrome-stable
        headless: true
        timeout_ms: 30000
    retry:
        enabled: false
        max_retries: 3
        delay_ms: 1000
    cache:                    # PSR-6 response cache
        enabled: false
        pool: cache.app
        ttl: 300
    rate_limit:
        enabled: false
        limit: 60
        interval: '1 minute'
    logging:
        enabled: false
        service: logger
```

## Quality

```bash
composer test       # PHPUnit (network tests excluded by default)
composer stan       # PHPStan (level max)
composer cs-check   # php-cs-fixer (@PSR12 + @Symfony), dry run
composer deptrac    # architecture/layer rules
composer qa         # all of the above
```

Run the live integration tests (they skip automatically when Cloudflare blocks the
current IP):

```bash
vendor/bin/phpunit --group network
```

## Credits

- Ported from [`tommhe14/sofascore-wrapper`](https://github.com/tommhe14/sofascore-wrapper) (Python, MIT).

## License

[MIT](LICENSE) © Aleksandr Ryzhkov
