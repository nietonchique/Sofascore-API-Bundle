# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2026-06-13

### Added
- `http.crypto_method` config option — the minimum TLS version (a
  `STREAM_CRYPTO_METHOD_TLSv1_*_CLIENT` constant), defaulting to TLS 1.3.

### Fixed
- SofaScore's Cloudflare returns a `403 "challenge"` on older TLS handshakes from
  some clients (notably inside containers), independently of headers/IP. The HTTP
  client now negotiates TLS 1.3 by default, which clears it. Override via
  `http.crypto_method` for a different minimum.

## [2.0.0] - 2026-06-13

### Changed
- **BC break:** the bundle now requires **PHP 8.4+** and **Symfony 8.0+**
  components, dropping support for PHP 8.2/8.3 and Symfony 7.x. It targets the
  current stack only.

### Removed
- The CI "lowest dependencies" job (no legacy-version support). CI now runs on
  PHP 8.4 and 8.5.

## [1.1.0] - 2026-06-13

### Added
- Chrome transport proxy support (`chrome.proxy`) and configurable warm-up URL
  (`chrome.warmup_url`), so the headless-Chrome fallback can use the same exit
  as the HTTP transport.
- New transport config options `http.user_agent` and `http.x_requested_with`.
- Configuration validation: value ranges (`http.timeout` ≥ 0, `cache.ttl` ≥ 0,
  `retry.*` ≥ 0, `rate_limit.limit` ≥ 1) and non-empty `base_url` / `chrome.binary`
  / `cache.pool` / `rate_limit.interval`.
- More tests: reflection coverage that every `SofascoreClient` factory returns an
  endpoint, every no-arg endpoint resolves from the container, invalid-config
  rejection, and chrome-proxy wiring.

### Changed
- CI: added a lowest-dependencies job (PHP 8.2 / Symfony 7.1 floor) and a coverage
  job enforcing an 80% line-coverage threshold.
- Documentation: new Error-handling and Troubleshooting sections; transport and
  configuration reference expanded.

## [1.0.2] - 2026-06-13

### Fixed
- `getPlayer()` and `getLeague()` now unwrap the `player` / `uniqueTournament`
  envelope before building the DTO — previously they produced all-null DTOs.
- `Tournament::$sport` is resolved from `category.sport` (SofaScore exposes no
  top-level sport on a unique tournament) — it was always `null` before.
- `Player::$jerseyNumber` is now `?string` (the API sends a string such as `"7"`);
  the integer is available as the new `shirtNumber`.

### Added
- Entity DTOs expanded with stably-present fields, verified against real API
  responses: Team (`fullName`, `gender`, `type`, `disabled`, `category`,
  `teamColors`), Player (`shirtNumber`, `height`, `dateOfBirth` + `Timestamp`,
  `gender`, `contractUntilTimestamp`, `deceased`), Tournament (`gender`,
  `primaryColorHex`, `secondaryColorHex`, `category`), Event (`statusCode`,
  `statusDescription`, `customId`).
- New `Category` and `TeamColors` DTOs.
- Real-response DTO validation tests backed by frozen fixtures (`tests/fixtures/dto/`).

## [1.0.1] - 2026-06-13

### Fixed
- `HttpClientTransport` now sends the `X-Requested-With` header that SofaScore's
  API requires; without it every request was answered with a Cloudflare `403`.
  The default HTTP transport now reaches the API directly from a non-blocked IP
  (verified live). The header value is a random per-instance token (the server
  does not validate it).

## [1.0.0] - 2026-06-13

### Added
- Initial release: a Symfony bundle and standalone PHP client for the unofficial
  SofaScore API, ported from the Python `sofascore-wrapper` library.
- Pluggable transport layer: `HttpClientTransport` (default), `ChromeTransport`
  (headless-Chrome fallback via `chrome-php/chrome`) and `ChainTransport`
  (HTTP → Chrome on Cloudflare 403).
- Opt-in transport decorators (disabled by default): PSR-6 caching, retry
  (`RetryableHttpClient`), rate limiting and PSR-3 logging.
- Endpoint groups covering all SofaScore modules: search, match, player, team,
  league, manager, transfers, news, user data, flag and the per-sport groups
  (basketball, tennis, american football, baseball, cricket, esports, ice hockey,
  MMA, motorsport, rugby).
- Typed DTOs for the core entities (`Event`, `Player`, `Team`, `Tournament`,
  plus `Country`, `Sport`, `Score`).
- Symfony bundle integration (`SofascoreApiBundle`) with autowired services and a
  configuration tree for the transport and decorators.

[2.0.0]: https://github.com/nietonchique/Sofascore-API-Bundle/releases/tag/v2.0.0
[1.1.0]: https://github.com/nietonchique/Sofascore-API-Bundle/releases/tag/v1.1.0
[1.0.2]: https://github.com/nietonchique/Sofascore-API-Bundle/releases/tag/v1.0.2
[1.0.1]: https://github.com/nietonchique/Sofascore-API-Bundle/releases/tag/v1.0.1
[1.0.0]: https://github.com/nietonchique/Sofascore-API-Bundle/releases/tag/v1.0.0
