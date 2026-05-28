# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [3.2.0] — 2026-05-27

**Complete rewrite.** Aligned with the Smobilpay partner API spec
v3.2.0. Package renamed from `maviance/smobilpay-php` to
`maviance/smobilpay-php-client`; PHP namespace renamed from
`Maviance\S3PApiClient\` to `Maviance\Smobilpay\`. There is no
in-place upgrade path — see [UPGRADING.md](./UPGRADING.md).

### Removed (breaking)

- HMAC request signing (`HMACSignature.php`). The S3P API no longer
  supports HMAC; OAuth 2.0 `client_credentials` is the only auth scheme.
- The auto-generated `ApiClient`, `Configuration`, `ObjectSerializer`,
  `HeaderSelector`, `ApiException`.
- All auto-generated `src/Service/*Api.php` classes.
- All auto-generated `src/Model/*.php` classes.
- All auto-generated `docs/Api/*.md` and `docs/Model/*.md`.

### Added

- `Maviance\Smobilpay\SmobilpayClient` — single facade with 5 API
  service groups: `masterdata()`, `accountValidation()`, `initiate()`,
  `confirm()`, `verify()`.
- `Maviance\Smobilpay\SmobilpayConfig` — readonly DTO with named-args
  constructor and immutable `with*()` derivations.
- `Maviance\Smobilpay\Auth\OAuth2TokenManager` + `OAuth2Token` —
  OAuth 2.0 mint + in-memory cache + **optional PSR-16 cross-request
  cache** (e.g. APCu, Redis via any PSR-16 adapter).
- `Maviance\Smobilpay\Http\HttpTransport` — PSR-18 BYO request engine
  with PSR-17 factories. Guzzle is suggested, never required.
- `Maviance\Smobilpay\Http\JsonSerializer` — hand-rolled reflection
  encoder/decoder for `readonly` DTOs. `#[ListOf(...)]` attribute for
  nested-list element typing.
- `Maviance\Smobilpay\Http\LenientDateParser` — date-tolerant parser;
  accepts ISO date / offset datetime / zoned / local datetime / instant
  variants.
- 27 `readonly` model DTOs + 6 enums covering every partner-spec
  endpoint, including the new `GET /v2/validate` →
  `CustomerAccount` with tri-state `Status` enum
  (`UNKNOWN`/`VALIDATED`/`VERIFIED`).
- Exception hierarchy: `SmobilpayException` (abstract base) →
  `SmobilpayApiException`, `SmobilpayAuthException`,
  `SmobilpayTransportException`, `SmobilpayParseException`.
  `SmobilpayConfigException` extends `\InvalidArgumentException` for
  caller-input errors.
- `bin/smoke-test` + `samples/SmokeTest.php` — 15-scenario smoke-test
  harness that reads a `smoke-test.json` config, emits one-line
  `RUN`/`PASS`/`SKIP`/`FAIL` headlines plus a full `(all fields)` dump
  of every parsed response DTO, and supports `--offline` (fixture
  replay) and `--strip-volatile` (timestamp/UUID/JWT scrubbing for
  reproducible diffs). Quote-only by default; every collection-bearing
  block (cashout, bill, topup, voucher, product, subscription, cashin)
  can opt in to a full `POST /v2/collectstd` + one-shot `/v2/verifytx`
  poll by setting `"collect": true` with `customerPhonenumber` +
  `customerEmailaddress`.
- Comprehensive test suite: unit tests for OAuth lifecycle, JSON
  serializer, date parser, query encoder, model invariants, exception
  payloads; integration tests for every API class using
  `php-http/mock-client` + JSON fixtures.
- GitHub Actions CI: PHP 8.2 / 8.3 / 8.4 × Composer
  `lowest`/`highest` matrix; lint, PHPStan level 8, PHPUnit,
  coverage ≥80% line on `src/` (excluding `src/Model/`), offline
  smoke-test self-check.
- README rewritten end-to-end for the v3 surface — section per concept,
  PHP idioms throughout.

### Changed

- PHP floor: ^8.2 (was ^8.1). Required for `readonly class`.
- Composer dependencies trimmed from "Guzzle hard dep + ramsey/uuid +
  CodeSniffer 2.6 + PHPUnit 4.8" to "10 PSR-interface deps + Ramsey
  UUID + modern dev tools (PHPUnit 11, PHPStan 2, PHP-CS-Fixer 3.65)".
- License retained: Apache-2.0.

### Migration

See [UPGRADING.md](./UPGRADING.md) for a step-by-step recipe from
v2.x HMAC users to v3.x OAuth 2.0.

---

## [2.2.2] and earlier

See git history. v2.x stays on Packagist as `maviance/smobilpay-php`
for partners not yet ready to move to OAuth 2.0.
