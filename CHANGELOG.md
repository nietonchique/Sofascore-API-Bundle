# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.0.1]: https://github.com/nietonchique/Sofascore-API-Bundle/releases/tag/v1.0.1
[1.0.0]: https://github.com/nietonchique/Sofascore-API-Bundle/releases/tag/v1.0.0
