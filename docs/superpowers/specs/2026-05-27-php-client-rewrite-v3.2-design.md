# PHP S3P Client Rewrite — v3.2 Design

**Status:** Approved — ready for implementation planning
**Date:** 2026-05-27
**Author:** Brainstorming session, michaelnowag@gmail.com
**Scope:** Complete rewrite of `maviance/smobilpay-php` (v2.x) into
`maviance/smobilpay-php-client` (v3.2.0), aligned with the partner-facing
S3P API spec at `/root/php-smobilpay-s3p-api/apidocs/s3p_3.2.0_openapi_specs_partner.yml`.

---

## Background and motivation

The current PHP client at `/root/s3p-clients/php` is OpenAPI-generator output
(circa 2018), hard-wired to HMAC request signing, with no tests, generated
table-of-endpoints documentation, and PHPUnit 4.8 / php_codesniffer 2.6 as
its dev toolchain. The S3P API has moved to v3.2.0 OAuth-2.0-only; a freshly
hand-written Java client exists at `/root/s3p-clients/java` as the cross-language
reference for what a partner-grade SDK should look like.

This rewrite delivers a hand-written, fully tested, partner-facing PHP client
that is the structural twin of the Java client — same flows, same documentation
shape, same smoke-test surface — but built on PHP-idiomatic foundations
(readonly DTOs, PSR interfaces, named arguments) rather than mirroring Java
1-to-1.

## Goals

1. Drop HMAC entirely; OAuth 2.0 `client_credentials` is the only auth scheme.
2. Cover every partner-spec endpoint with a hand-written, typed PHP surface.
3. Be production-grade: PSR-12, PHP 8.2 readonly DTOs, PSR-18/17/16/3 interfaces,
   PHPStan level 8, 80%+ line coverage on `src/` (90%+ branch on Auth/Http).
4. Ship a smoke test that is bit-for-bit parity with Java's `runSmokeTest`,
   readable from the same `smoke-test.json` config, so the PHP and Java clients
   can be diffed side by side against the same partner environment.
5. README that reads as the structural twin of Java's README, section for section.
6. Keep dependency footprint minimal — no hard dep on Guzzle, Symfony, or any
   serializer library.

## Non-goals (explicitly out of scope)

- HMAC backward compatibility (clean break — documented in CHANGELOG + UPGRADING)
- Regenerating from `openapi-generator` (hand-written is the point)
- Symfony / Laravel framework bridges (DI-friendly core only; bridges are a
  separate future package)
- Calling `/v2/collectstd` from CI or the smoke test (real money movement is
  never automated)
- Webhook receiver / callback signature verification helpers
- Async / promise-based API (sync PSR-18 only)
- Partner-facing CLI tool beyond the developer-internal smoke test

---

## Part 1 — Architecture and file layout

### Project identity

| Field        | Value                                |
|--------------|--------------------------------------|
| Composer     | `maviance/smobilpay-php-client`      |
| Namespace    | `Maviance\Smobilpay\`                |
| Version      | `3.2.0`                              |
| PHP floor    | `^8.2`                               |
| License      | Apache-2.0 (kept)                    |
| Branch       | `feature/rewrite-v3.2-oauth2-only` (matches Java's branch name) |

### Layered architecture

```
Maviance\Smobilpay\
├── SmobilpayClient            facade (constructs + holds 5 API services)
├── SmobilpayConfig            readonly DTO (baseUrl, publicKey, secretKey,
│                              apiVersion, requestTimeout, tokenRefreshSkew,
│                              optional PSR-16 token cache, optional PSR-3 logger,
│                              optional PSR-18 client, PSR-17 factories)
├── Exception\
│   ├── SmobilpayException             abstract base (extends \RuntimeException)
│   ├── SmobilpayApiException          httpStatus + ApiError|null + rawBody
│   ├── SmobilpayAuthException         httpStatus + oauthError|null
│   ├── SmobilpayTransportException    wraps PSR-18 ClientExceptionInterface
│   └── SmobilpayConfigException       extends \InvalidArgumentException — for
│                                      config validators + API arg validators
├── Auth\
│   ├── OAuth2Token            readonly DTO (accessToken, tokenType, expiresAt)
│   └── OAuth2TokenManager     mint + in-memory cache + optional PSR-16 persistence
├── Http\
│   ├── HttpTransport          PSR-18 request/response engine (get/post,
│   │                          auth header injection, error envelope unwrap)
│   ├── QueryParams            null-skipping URL-encoded query builder
│   ├── JsonSerializer         encode/decode + readonly-class hydrator
│   │                          (hand-rolled, ~150 lines, no third-party dep)
│   ├── LenientDateParser      parses ISO date / offset datetime / zoned /
│   │                          local datetime / instant → DateTimeImmutable
│   └── SystemClock            internal \Psr\Clock\ClockInterface impl, ~5 LOC
├── Api\
│   ├── MasterdataApi          merchants(), services(), products(?serviceid),
│   │                          vouchers(?serviceid), topups(?serviceid),
│   │                          cashins(?serviceid), cashouts(?serviceid)
│   ├── AccountValidationApi   verifyServiceNumber(merchant, serviceid, serviceNumber),
│   │                          validateAccount(destination, serviceId)
│   ├── InitiateApi            bills(merchant, serviceid, serviceNumber),
│   │                          subscriptions(merchant, serviceid, ?serviceNumber, ?customerNumber),
│   │                          quote(QuoteRequest)
│   ├── ConfirmApi             collect(CollectionRequest)
│   └── VerifyApi              ping(), account(),
│                              verifyTransaction(?ptn, ?trid),
│                              historyByPtn(ptn),
│                              historyByTrid(trid),
│                              historyByDateRange(from, to)
└── Model\                     ~25 readonly classes (DTOs/value objects)
    Account, AmountType (enum), ApiError, Bill, BillType (enum), Cashin,
    Cashout, CollectionRequest, CollectionResponse, Commission,
    CustomerAccount + CustomerAccount\Status (enum UNKNOWN/VALIDATED/VERIFIED),
    I18nText, Merchant, MerchantStatus (enum), PaymentItem (interface),
    PaymentStatus, PaymentStatusType (enum), Ping, Product, QuoteRequest,
    QuoteResponse, Service, ServiceStatus (enum), ServiceType (enum),
    Subscription, Topup
```

### Repo-level file layout

```
/                         (existing repo root)
├── composer.json         rewritten (new deps, new namespace, v3.2.0)
├── README.md             rewritten in Java client's narrative style (~450 lines)
├── CHANGELOG.md          new — documents 2.x → 3.x breaking rewrite
├── UPGRADING.md          new — migration guide for v2 HMAC users
├── CONTRIBUTING.md       new — conventional commits, lint/stan/coverage gates
├── LICENSE               keep (Apache-2.0)
├── phpunit.xml           rewritten for PHPUnit 11
├── phpstan.neon          new — level 8
├── .php-cs-fixer.php     new — PSR-12 ruleset
├── .github/workflows/    new — ci.yml (lint + stan + test + coverage + matrix)
├── src/                  WIPED + rebuilt per the layered architecture above
├── tests/
│   ├── Unit/             one test class per src class
│   ├── Integration/      per-API tests using PSR-18 MockClient + JSON fixtures
│   └── Fixtures/         JSON response samples (one per endpoint)
├── docs/
│   ├── superpowers/specs/2026-05-27-php-client-rewrite-v3.2-design.md
│   │                     this spec
│   └── examples/         runnable example scripts per flow
│                         (cashout.php, bill.php, topup.php, voucher.php,
│                          product.php, subscription.php, cashin.php,
│                          verify.php, validate.php)
├── samples/              parallel to Java's src/samples/
│   ├── SmokeTest.php     harness (15 scenarios, same order/output as Java)
│   └── SmokeTestConfig.php  hydrates the same smoke-test.json that Java reads
├── bin/
│   └── smoke-test        #!/usr/bin/env php shim → samples/SmokeTest::main($argv)
└── smoke-test.example.json  IDENTICAL format & field names to Java's
```

The old `src/Service/`, `src/Model/`, `docs/Api/`, `docs/Model/`, the old
`HMACSignature.php`, the old `ApiClient.php`/`Configuration.php`/`ObjectSerializer.php`
are all deleted wholesale.

---

## Part 2 — Tech stack and dependencies

### Runtime (composer.json `require`)

| Package              | Version                          | Purpose                                                              |
|----------------------|----------------------------------|----------------------------------------------------------------------|
| `php`                | `^8.2`                           | readonly classes, enum, constructor property promotion               |
| `ext-curl`           | `*`                              | Underpins most PSR-18 implementations                                |
| `ext-json`           | `*`                              | Wire format                                                          |
| `ext-mbstring`       | `*`                              | UTF-8 safe string ops for query encoding & redaction                 |
| `psr/http-client`    | `^1.0`                           | PSR-18 — partner-supplied HTTP client (BYO)                          |
| `psr/http-factory`   | `^1.0`                           | PSR-17 — request/stream/uri factories (BYO)                          |
| `psr/http-message`   | `^1.1 \|\| ^2.0`                 | PSR-7 — message interfaces                                           |
| `psr/simple-cache`   | `^1.0 \|\| ^2.0 \|\| ^3.0`       | PSR-16 — *optional* token cache; interface only                      |
| `psr/log`            | `^1.1 \|\| ^2.0 \|\| ^3.0`       | PSR-3 — *optional* logger; interface only                            |
| `psr/clock`          | `^1.0`                           | PSR-20 — injectable Clock for `OAuth2TokenManager` testability       |
| `ramsey/uuid`        | `^4.7`                           | `CollectionRequest.quoteId` is a UUID                                |

Total: **10 packages**; 7 are PSR interface-only with no transitive bloat.

### Suggested (composer.json `suggest`)

Printed by `composer install` so partners know what to pair the library with:

| Package                                  | Suggestion                                                              |
|------------------------------------------|-------------------------------------------------------------------------|
| `guzzlehttp/guzzle`                      | `^7.5 — Recommended PSR-18 client. Also provides PSR-17 factories.`     |
| `php-http/curl-client` + `nyholm/psr7`   | `Lighter alternative if your project doesn't already use Guzzle.`        |
| `symfony/http-client` + `nyholm/psr7`    | `Use if your project is already on the Symfony stack.`                   |
| `symfony/cache` / `cache/apcu-adapter`   | `PSR-16 implementations to persist OAuth tokens across PHP requests.`    |
| `monolog/monolog`                        | `PSR-3 logger if you want client wire-level logs.`                       |

### Dev (composer.json `require-dev`)

| Package                            | Version    | Purpose                                                          |
|------------------------------------|------------|------------------------------------------------------------------|
| `phpunit/phpunit`                  | `^11.5`    | Project test runner (current is stuck on 4.8)                    |
| `phpstan/phpstan`                  | `^2.1`     | Static analysis, level 8 in `phpstan.neon`                       |
| `friendsofphp/php-cs-fixer`        | `^3.65`    | PSR-12 formatting; replaces ancient `squizlabs/php_codesniffer`  |
| `php-http/mock-client`             | `^1.6`     | PSR-18 mock client for Integration tests (zero network)          |
| `nyholm/psr7`                      | `^1.8`     | Lightweight PSR-7/17 impl for tests + smoke test default         |
| `symfony/cache`                    | `^7.2`     | PSR-16 impl used in OAuth2TokenManager cache tests               |

### CI (`.github/workflows/ci.yml`)

Matrix: PHP `8.2` / `8.3` / `8.4` × Composer `lowest` / `highest`.

Stages, in order, each gating the next:

1. `composer validate --strict`
2. `composer install`
3. `vendor/bin/php-cs-fixer fix --dry-run --diff`
4. `vendor/bin/phpstan analyse --memory-limit=512M`
5. `vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml`
6. Coverage gate enforcement: ≥80% line coverage on `src/` (excluding `src/Model/`),
   ≥90% branch coverage on `src/Auth/` and `src/Http/`

### Diff vs current stack

- **Drop** `guzzlehttp/guzzle` hard dep → moved to `suggest` (PSR-18 BYO)
- **Drop** auto-generated `ApiClient` / `HMACSignature` / `HeaderSelector` /
  `ObjectSerializer` / old `Configuration`
- **Drop** PHPUnit 4.8 (2015, no PHP 8 support) → PHPUnit 11
- **Drop** `squizlabs/php_codesniffer 2.6` (2016) → PHP-CS-Fixer 3.65
- **Add** PHPStan level 8 (wasn't in the project)
- **Add** PSR-16, PSR-3, PSR-20 interface deps (optional cache + optional log +
  injectable clock)

---

## Part 3 — Auth and transport runtime details

### OAuth 2.0 token lifecycle

`OAuth2TokenManager` is the single point that touches `/oauth/token`. It
composes a PSR-18 client, a PSR-17 request factory, the `SmobilpayConfig`,
an injectable `\Psr\Clock\ClockInterface` (defaults to internal `SystemClock`),
and an optional `\Psr\SimpleCache\CacheInterface`.

**Mint request** — POST `{baseUrl}/oauth/token`:

```
Headers:
  Authorization:  Basic base64(publicKey:secretKey)
  Content-Type:   application/x-www-form-urlencoded
  Accept:         application/json
Body:
  grant_type=client_credentials
```

Response `{access_token, expires_in, token_type}` is hydrated into a readonly
`OAuth2Token { string accessToken, string tokenType, DateTimeImmutable expiresAt }`.
`expiresAt = mintedAt + expires_in seconds`.

**Get-or-mint** (`accessToken(): string`):

1. Read cached `OAuth2Token` from in-memory slot first.
2. If empty and PSR-16 cache configured → try `$cache->get($cacheKey)`.
   Key shape: `smobilpay.oauth2.token.<sha256(baseUrl + ':' + publicKey)>`.
3. If still empty *or* `$token->isExpired($now, $config->tokenRefreshSkew)`
   → mint via the POST above.
4. After mint: write to in-memory slot AND (if PSR-16 configured)
   `$cache->set($cacheKey, $token, ttl: $expiresIn - skewSeconds)`.

**Concurrency.** PHP-FPM workers are single-threaded per request, so no
`synchronized` block needed inside one process. Cross-process race (two
workers minting at the same time) is acceptable — both succeed, last write
wins, both tokens stay valid until expiry. One-line code comment documents
this so a future reader doesn't add file locking.

**Manual refresh.** `$client->tokens()->refresh()` discards the cache entry
(in-memory and PSR-16) and mints unconditionally. Used by the smoke test's
"OAuth 2.0 token refresh" scenario.

**Token errors → `SmobilpayAuthException`** with `httpStatus`, `oauthError`
(parsed from the `error` field of the OAuth error envelope), and human
message. Matches Java's exception shape so smoke-test FAIL lines diff cleanly.

### HTTP transport

`HttpTransport` wraps the PSR-18 client. Public surface (used by the 5
`Api\*` classes):

```php
public function get(string $path, QueryParams $query, string|array $responseType): mixed;
public function post(string $path, object $body, string $responseType): mixed;
```

`$responseType` is either a class name (`Bill::class`) or `[Bill::class]` for
a list response — the array shorthand replaces Java's
`TypeReference<List<Bill>>` boilerplate.

**Per-call recipe:**

1. Resolve URI: `rtrim(baseUrl, '/') . $path .
   ($query->isEmpty() ? '' : '?' . $query->encode())`.
2. Get bearer from `OAuth2TokenManager` (which mints/caches as above).
3. Build request via PSR-17 `RequestFactoryInterface` + `StreamFactoryInterface`,
   attach headers:
   - `Authorization: Bearer <jwt>`
   - `x-api-version: 3.0.0` (from `$config->apiVersion`)
   - `Accept: application/json`
   - On POST: `Content-Type: application/json`
4. Dispatch via PSR-18 client.
5. Parse response:
   - 2xx → `JsonSerializer->decode($body, $responseType)`
   - non-2xx → try parse body as `ApiError`; throw
     `SmobilpayApiException($status, ?ApiError, $rawBody)`.
6. PSR-18 `ClientExceptionInterface` → rewrap as `SmobilpayTransportException`.

**Timeouts.** PSR-18 does not standardize timeouts. The library documents that
partners configure them on their chosen HTTP client and pass it in.
`$config->requestTimeout` is kept and surfaced via a getter so DI helpers
(or the smoke test) can configure Guzzle accordingly, but the transport
itself does not try to set it.

### Query-string semantics

`QueryParams` (null-skipping builder):

```php
QueryParams::of()
    ->add('merchant', $merchant)         // string
    ->add('serviceid', $serviceid)       // int
    ->add('serviceNumber', $maybeNull)   // null → skipped
    ->add('timestamp_from', $from->format(DATE_ATOM));
```

`encode()` uses `rawurlencode()` (matches Java's `URLEncoder.encode` with
UTF-8). Insertion order is preserved for stable, snapshot-testable URLs.

### JSON serialization

`JsonSerializer` is hand-rolled (~150 lines, zero third-party serializer dep).

- **Encode.** Reflect public properties of the readonly class, skip `null`
  (matches Jackson's `@JsonInclude(NON_NULL)`), then `json_encode(...,
  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)`.
- **Decode.** Reflect target constructor, map JSON keys to constructor
  parameters by name. Coerce primitives, recurse into typed object
  parameters, hydrate `BackedEnum` from `value`, hydrate `DateTimeImmutable`
  through `LenientDateParser`, hydrate `UuidInterface` via
  `Ramsey\Uuid\Uuid::fromString`. Unknown JSON keys are ignored (forward
  compatibility — matches Java's `FAIL_ON_UNKNOWN_PROPERTIES=false`).

`LenientDateParser` accepts, in order: ISO local date (`YYYY-MM-DD`), ISO
offset datetime (`YYYY-MM-DDTHH:MM:SS±HH:MM`), ISO zoned (`...Z`), naked
local datetime (`YYYY-MM-DDTHH:MM:SS`), and Instant-style trailing
fractional seconds. Returns `DateTimeImmutable` or null for blank/null input.
This mirrors Java's `LenientLocalDateDeserializer` and is the same workaround
for the acceptance environment occasionally emitting date fields as offset
datetimes.

### Exception hierarchy

```
\RuntimeException
└── SmobilpayException                    abstract base
    ├── SmobilpayApiException             httpStatus + ?ApiError + rawBody
    ├── SmobilpayAuthException            httpStatus + ?oauthError
    └── SmobilpayTransportException       wraps PSR-18 ClientExceptionInterface

\InvalidArgumentException
└── SmobilpayConfigException              config validators + Api arg validators
```

---

## Part 4 — Testing strategy

### Test pyramid

```
samples/SmokeTest.php       ←  manual, real network, 15 scenarios, parity with Java
─────────────────────────────────────────────────────────────────────────────
tests/Integration/          ←  PSR-18 MockClient, JSON fixtures, ~30 tests
                               (per-API request/response round-trip)
─────────────────────────────────────────────────────────────────────────────
tests/Unit/                 ←  no I/O, ~80 tests
                               (OAuth2Token, OAuth2TokenManager, QueryParams,
                                JsonSerializer, every Model invariant,
                                LenientDateParser, every Exception type)
```

### Unit tests (`tests/Unit/`)

One test class per source class. Highest-value targets:

| Test class                  | Coverage                                                                                                              |
|-----------------------------|-----------------------------------------------------------------------------------------------------------------------|
| `OAuth2TokenTest`           | `isExpired($now, $skew)` truth table — expired, fresh, exactly-at-skew boundary, just-past-skew, far-future           |
| `OAuth2TokenManagerTest`    | First call mints; second within TTL reuses; refresh() forces re-mint; within-skew triggers re-mint; PSR-16 hit short-circuits HTTP; miss falls through then writes back; injectable `FakeClock` controls time |
| `OAuth2TokenManagerErrorTest` | OAuth `invalid_client` → `SmobilpayAuthException` w/ `oauthError='invalid_client'`; missing `access_token` → exception; malformed JSON → exception; HTTP 502 → exception w/ status=502 |
| `QueryParamsTest`           | Null skipping; rawurlencode of spaces/unicode/`+`/`&`/`=`; insertion order preserved; empty → empty string; int/float/bool/UUID stringification |
| `JsonSerializerTest`        | Encode skips nulls; decode populates readonly DTO via constructor; lenient date parser table-driven over 8 inputs incl. `2025-11-05T00:00:00+01:00`; BackedEnum hydration; UUID hydration; unknown JSON keys ignored; missing required → typed exception |
| `SmobilpayConfigTest`       | Missing baseUrl/publicKey/secretKey throw; defaults applied (apiVersion=3.0.0, timeout=30s, skew=30s); fluent setter overrides |
| `Model\*Test` (~25)         | Each readonly DTO: constructor accepts representative payload, getters return what was passed, `CollectionRequest` rejects `tag > 50` and `callbackUrl > 255`, `CustomerAccount::Status` enum cases match wire values |
| `Exception\*Test`           | Each exception type carries documented fields; `getMessage()` includes useful diagnostics                              |

### Integration tests (`tests/Integration/`)

One test class per `Api\*` class. Built on `php-http/mock-client` (PSR-18
in-memory mock) + fixture JSON files captured from the partner spec's
`examples:` blocks.

```
tests/
├── Integration/
│   ├── MasterdataApiTest.php
│   ├── AccountValidationApiTest.php
│   ├── InitiateApiTest.php
│   ├── ConfirmApiTest.php
│   └── VerifyApiTest.php
└── Fixtures/
    ├── ping.json
    ├── account.json
    ├── merchants.json
    ├── services.json
    ├── cashout.json, cashin.json, topup.json, voucher.json, product.json
    ├── bill.json, subscription.json
    ├── quote-request.json, quote-response.json
    ├── collect-request.json, collect-response.json
    ├── verifytx.json, history.json
    ├── customer-account.json, customer-account-401.json
    ├── error-respcode-41004.json
    ├── error-respcode-40408.json
    └── oauth-token.json, oauth-error-invalid-client.json
```

Each Integration test asserts **both directions**:

1. The PSR-7 request the client *sent* (method, URL, headers including
   `Authorization: Bearer ...` and `x-api-version: 3.0.0`, query string, body).
2. The hydrated response object matches the fixture's expected field values.

Example shape:

```php
public function testCashouts(): void
{
    $mock = new \Http\Mock\Client();
    $mock->addResponse($this->jsonResponse(200, 'cashout.json'));
    $client = $this->makeClient($mock);

    $result = $client->masterdata()->cashouts(serviceid: 999999);

    $request = $mock->getLastRequest();
    self::assertSame('GET', $request->getMethod());
    self::assertStringEndsWith('/v2/cashout?serviceid=999999', (string) $request->getUri());
    self::assertSame('3.0.0', $request->getHeaderLine('x-api-version'));
    self::assertStringStartsWith('Bearer ', $request->getHeaderLine('Authorization'));

    self::assertCount(1, $result);
    self::assertSame('FAKEPAYITEM-001', $result[0]->payItemId);
    self::assertSame(500.0, $result[0]->amountLocalCur);
}
```

### Fixture sourcing

In order:

1. `examples:` blocks inside `apidocs/s3p_3.2.0_openapi_specs_partner.yml`
   (canonical).
2. Real responses captured during a smoke-test dry run, scrubbed of customer
   PII (one-off helper script `scripts/capture-fixtures.php`).
3. Synthetic fixtures hand-written from the spec schemas where (1) and (2)
   are missing.

The same fixtures back the Integration tests AND become the reference
responses for `samples/SmokeTest.php` when run in `--offline` mode.

### Smoke test parity (offline mode)

Beyond running against a real partner environment, the smoke test gets an
`--offline` flag that swaps the PSR-18 client for a `MockClient` loaded with
`tests/Fixtures/*.json`. Three useful invocations:

```bash
# 1. Online — same as Java's runSmokeTest. Real network.
composer smoke

# 2. Online + strip volatile lines for diff vs Java
composer smoke -- --strip-volatile > /tmp/php.log
( cd ../java && ./gradlew runSmokeTest -q --console=plain | sed -E '...' ) > /tmp/java.log
diff -u /tmp/java.log /tmp/php.log

# 3. Offline — no network, replays fixtures. Used in CI to smoke-test the
#    smoke-test harness itself + as a self-documenting demo for partners.
composer smoke -- --offline
```

The `--strip-volatile` filter scrubs (in both Java and PHP output) the same
set of timestamp / JWT-prefix / UUID / PTN lines so a clean side-by-side is
possible.

### Coverage gate

`composer test:coverage` runs PHPUnit with Xdebug or PCOV and fails the
build if:

- Line coverage on `src/` (excluding `src/Model/`) drops below **80%** —
  same threshold and same exclusion as Java's Jacoco `INSTRUCTION` 80% gate.
- Branch coverage on `src/Auth/` and `src/Http/` drops below **90%** —
  these are the high-risk paths; mirrors a tighter gate on the auth/transport
  subset.

CI enforces the same gates via the same Composer script.

### What we don't test

- The real partner API beyond the smoke test (out of CI's reach by design —
  no shared partner secrets in GitHub Actions).
- PSR-18 implementations (Guzzle/Symfony/etc.) — they have their own tests.
- Concurrency under PHP-FPM (no shared-memory threading in PHP; the
  cross-process mint race documented in the code is benign).

---

## Part 5 — Documentation and migration

### README

The current `README.md` is 107 lines of auto-generated table-of-endpoints
plus a 20-line snippet. The Java client's README is 470 lines of narrative
documentation organized by **flow**, not by HTTP path. The PHP README will
mirror the Java structure section for section, with PHP idioms in every
example, so a partner who already integrated the Java client can navigate the
PHP README by muscle memory.

**Section order** (matches Java's README):

1. What this client does
2. Requirements
3. Installation
4. Quick start
5. Authentication
6. Choosing the right flow (table)
7. Conventions
8. Collection — cash-out
9. Collection — bill payment
10. Collection — airtime top-up
11. Collection — voucher purchase
12. Collection — product purchase
13. Collection — subscription top-up
14. Disbursement — cash-in
15. Pre-payment verification (verifyServiceNumber + validateAccount + KYC note)
16. Catalog discovery
17. Account and ping utilities
18. Historical lookups
19. Error handling
20. Configuration reference (table)
21. Onboarding
22. Development
23. License

Target: ~450 lines, within 5% of Java's, prose lifted-and-translated section
by section.

### Other docs in the repo

| File                                                                | Purpose                                                                                  |
|---------------------------------------------------------------------|------------------------------------------------------------------------------------------|
| `CHANGELOG.md`                                                      | 2.x → 3.x as a single breaking change with explicit removed/added lists                  |
| `UPGRADING.md`                                                      | Step-by-step migration for v2 HMAC users (incl. method-mapping table)                    |
| `docs/superpowers/specs/2026-05-27-php-client-rewrite-v3.2-design.md` | This spec (committed for traceability)                                                  |
| `docs/examples/*.php`                                               | One runnable PHP file per flow (cashout, bill, topup, voucher, product, subscription, cashin, verify, validate) |
| `samples/`, `bin/smoke-test`, `smoke-test.example.json`              | Smoke-test harness with parity to Java                                                   |
| `CONTRIBUTING.md`                                                   | Conventional commits, PSR-12 enforced via php-cs-fixer, PHPStan level 8, 80% coverage    |

The auto-generated `docs/Api/*.md` and `docs/Model/*.md` are deleted with
the old `src/`.

### Migration story (v2 → v3) at a glance

Three audiences:

1. **Existing partners on `maviance/smobilpay-php` v2.x using HMAC.** Keep
   working on v2 (Packagist still serves it). When ready to move to OAuth2:
   change composer entry to `maviance/smobilpay-php-client: ^3.2`, follow
   `UPGRADING.md`'s recipe, swap HMAC env vars for OAuth public/secret keys
   (obtained from Maviance support during re-onboarding). v2 stays on the old
   name; v3 ships under the new name. No automatic upgrade path — explicit
   `composer require`.
2. **New partners.** Land on the new README, follow Quick Start, never see HMAC.
3. **Maviance support during partner onboarding.** Updated checklist: hand
   out OAuth credentials (not HMAC), point partners at the new README's
   Quick Start, share `smoke-test.example.json` as the recommended first
   integration check.

### What ships when this design is implemented

- A `src/` of ~30 hand-written PHP files, every one tested
- ~110 tests, 80%+ line coverage on `src/` (≥90% branch coverage on Auth/Http)
- A `samples/SmokeTest.php` that runs the same 15 scenarios as Java's, reads
  the same JSON config, emits diff-friendly output
- A `README.md` that reads as the structural twin of the Java README
- A `composer.json` with 10 hard deps (7 PSR interfaces + 3 small libs),
  6 dev deps
- A CI pipeline that lints, statically analyzes, tests, gates coverage
- Old HMAC code, old generated Service/Model classes, old auto-docs all gone

---

## Smoke-test scenarios (verbatim from Java, for PHP parity)

Read-only / quote-only. Never calls `/v2/collectstd`. Each scenario emits one
`RUN`, then indented `detail()` lines, then exactly one of `PASS`, `SKIP`, or
`FAIL`. Final line is a summary `Summary: N passed, M skipped, K failed`. Exit
codes: 0 (all passed/skipped), 1 (one or more failed), 2 (config error before
client could start).

1. **Ping** — auth probe; emits server time, version, nonce, public-key echo.
2. **OAuth 2.0 token refresh** — caches first bearer, forces refresh, re-pings;
   logs first/forced bearer prefixes (12 chars) and whether identical.
3. **Account profile** — agent name + id, company, balance + currency, daily
   limit max + remaining.
4. **Merchant catalog** — total + first 5 merchants with country and status.
5. **Service catalog** — total + distribution by service type + lists of
   VOUCHER / SUBSCRIPTION / verifiable services (helpful for filling out
   per-flow blocks in `smoke-test.json`).
6. **Collection — cash-out** — discover from `masterdata().cashouts(serviceId)`,
   quote, report; skip if no `cashout` block configured.
7. **Collection — bill payment** — discover from
   `initiate().bills(merchant, serviceId, serviceNumber)`, quote with
   `bill.amountLocalCur`, report; skip if no `bill` block.
8. **Collection — airtime top-up** — discover from
   `masterdata().topups(serviceId)`, quote with configured amount or
   catalog amount, report; skip if no `topup` block.
9. **Collection — voucher purchase** — discover from
   `masterdata().vouchers(serviceId)`. **Special**: SKIP on
   `respCode == 41004` (catalog labels service VOUCHER but `/v2/voucher`
   rejects).
10. **Collection — product purchase** — discover from
    `masterdata().products(serviceId)`, quote, report; skip if no `product` block.
11. **Collection — subscription top-up** — discover from
    `initiate().subscriptions(merchant, serviceId, ?serviceNumber, ?customerNumber)`,
    quote, report; skip if no `subscription` block, or if both keys are null.
12. **Disbursement — cash-in** — discover from
    `masterdata().cashins(serviceId)`, quote, report; skip if no `cashin` block.
13. **Account validation — verifyServiceNumber** — boolean
    `accountValidation().verifyServiceNumber(merchant, serviceId, serviceNumber)`;
    **Special**: SKIP on `respCode == 40408` (service does not support
    pre-payment verification).
14. **Account validation — validate destination** —
    `accountValidation().validateAccount(destination, serviceId)` returning
    `CustomerAccount` (destination, status, name); **Special**: SKIP on
    HTTP 401 (restricted endpoint, partner not enabled).
15. **History — last 7 days** —
    `verify().historyByDateRange(today.minusDays(7), today)`; total + first 3
    rows with PTN, status, price + currency, trid.

### `smoke-test.json` schema (identical to Java)

```jsonc
{
  "baseUrl":    "https://api.example.invalid",
  "publicKey":  "...",
  "secretKey":  "...",
  "apiVersion": "3.0.0",        // optional, defaults to 3.0.0

  "cashout":      { "serviceId": 999999, "amount": 500 },
  "bill":         { "merchant": "CDE",   "serviceId": 4321,   "serviceNumber": "METER-001" },
  "topup":        { "serviceId": 100200, "amount": 500 },
  "voucher":      { "serviceId": 100300, "amount": null },
  "product":      { "serviceId": 100400, "amount": null },
  "subscription": { "merchant": "CANALPLUS", "serviceId": 100500,
                    "serviceNumber": "DECODER-001234", "customerNumber": null,
                    "amount": null },
  "cashin":       { "serviceId": 100600, "amount": 10000 },
  "verify":       { "merchant": "ENEO",  "serviceId": 1234,  "serviceNumber": "01234567" },
  "validate":     { "destination": "237699999999", "serviceId": 999999 }
}
```

### Path resolution (identical to Java)

`arg[0]` → env `SMOBILPAY_SMOKE_CONFIG` → `./smoke-test.json` in CWD.

### Redaction (identical to Java)

`baseUrl` stripped of trailing `/`; keys redacted to `first4...last2`
(or `****` if ≤4 chars).

---

## Appendix A — Endpoint coverage matrix

| Spec endpoint                  | Method | API class               | PHP method                                  |
|--------------------------------|--------|-------------------------|---------------------------------------------|
| `/oauth/token`                 | POST   | (internal)              | `OAuth2TokenManager::accessToken()`         |
| `/v2/ping`                     | GET    | `VerifyApi`             | `ping()`                                    |
| `/v2/account`                  | GET    | `VerifyApi`             | `account()`                                 |
| `/v2/merchant`                 | GET    | `MasterdataApi`         | `merchants()`                               |
| `/v2/service`                  | GET    | `MasterdataApi`         | `services()`                                |
| `/v2/product`                  | GET    | `MasterdataApi`         | `products(?serviceid)`                      |
| `/v2/voucher`                  | GET    | `MasterdataApi`         | `vouchers(?serviceid)`                      |
| `/v2/topup`                    | GET    | `MasterdataApi`         | `topups(?serviceid)`                        |
| `/v2/cashin`                   | GET    | `MasterdataApi`         | `cashins(?serviceid)`                       |
| `/v2/cashout`                  | GET    | `MasterdataApi`         | `cashouts(?serviceid)`                      |
| `/v2/bill`                     | GET    | `InitiateApi`           | `bills(merchant, serviceid, serviceNumber)` |
| `/v2/subscription`             | GET    | `InitiateApi`           | `subscriptions(merchant, serviceid, ?serviceNumber, ?customerNumber)` |
| `/v2/quotestd`                 | POST   | `InitiateApi`           | `quote(QuoteRequest)`                       |
| `/v2/collectstd`               | POST   | `ConfirmApi`            | `collect(CollectionRequest)`                |
| `/v2/verifytx`                 | GET    | `VerifyApi`             | `verifyTransaction(?ptn, ?trid)`            |
| `/v2/historystd` by PTN        | GET    | `VerifyApi`             | `historyByPtn(ptn)`                         |
| `/v2/historystd` by TRID       | GET    | `VerifyApi`             | `historyByTrid(trid)`                       |
| `/v2/historystd` by date range | GET    | `VerifyApi`             | `historyByDateRange(from, to)`              |
| `/v2/verify`                   | GET    | `AccountValidationApi`  | `verifyServiceNumber(merchant, serviceid, serviceNumber)` |
| `/v2/validate`                 | GET    | `AccountValidationApi`  | `validateAccount(destination, serviceId)`   |

Total: 20 endpoints, including the OAuth token endpoint.

## Appendix B — Decision log

| # | Decision                                                | Alternative considered                                 |
|---|---------------------------------------------------------|--------------------------------------------------------|
| 1 | PHP-idiomatic redesign, not 1:1 Java mirror              | Mirror Java 1:1 / Hybrid                               |
| 2 | Package: `maviance/smobilpay-php-client`                 | Keep `maviance/smobilpay-php` / `…-api-php-client`     |
| 3 | PHP 8.2 floor                                            | PHP 8.1 / PHP 8.3                                      |
| 4 | PSR-18 BYO HTTP client + Guzzle as suggestion            | Hard-dep Guzzle / Hard-dep Symfony HttpClient          |
| 5 | In-memory + optional PSR-16 token cache                  | In-memory only / PSR-6 instead of PSR-16               |
| 6 | Single `SmobilpayClient` facade + per-API services       | Per-API services only / Both with services primary     |
| 7 | Hand-rolled `JsonSerializer`                             | `symfony/serializer` / `jms/serializer`                |
| 8 | Smoke-test parity with Java (same JSON, same output)     | PHP-shaped CLI with its own config schema              |
| 9 | Apache-2.0 license retained                              | MIT (matches php-smobilpay-s3p-api server)             |
| 10 | Branch `feature/rewrite-v3.2-oauth2-only` (matches Java) | Stay on `develop`                                      |
