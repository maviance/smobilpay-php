# smobilpay-php-client

PHP client library for the **Smobilpay partner API** (v3.2.0).

This is the curated, partner-facing client. It covers every endpoint a
partner integrator needs to move money in and out, sell value-added
services, and drive a payment UI from the static catalog.

> The sibling Java client at `org.maviance:smobilpay-java-client:3.2.0`
> exposes the same flows, the same scenarios, and the same smoke-test
> JSON config — so this PHP client and that Java client can be
> diff-compared against the same partner environment.

## What this client does

- **Payment collections.** Take payment from a customer's mobile wallet
  via a quote-then-confirm flow. Money flows *out* of the customer's
  wallet against a `Cashout` item. Works for cash-out (generic
  mobile-money collection), bill payment, top-up, voucher purchase,
  product purchase, and subscription top-up.
- **Disbursements.** Send funds out to a recipient's mobile wallet using
  the same quote-then-confirm flow against a `Cashin` item. Money flows
  *into* the recipient's wallet.
- **Account and service discovery.** Retrieve the static catalog of
  merchants, services, products, and payment items needed to drive a
  payment UI.
- **Status verification.** Look up the live status of a previously
  issued transaction by `ptn` or by your own custom `trid`, and search
  historical activity by date range.
- **Pre-payment account validation.** Check that a customer's service
  number is well-formed and accepted by the merchant before quoting.

## Requirements

- **PHP 8.2 or newer** at runtime.
- `ext-curl`, `ext-json`, `ext-mbstring`.
- A **PSR-18** HTTP client implementation + matching **PSR-17** factories
  (BYO — see Installation below).
- Network access to the base URL issued by Maviance support.
- An OAuth 2.0 credential pair (`publicKey` / `secretKey`) issued during
  partner onboarding.

The library hard-depends only on PSR interface packages
(`psr/http-client`, `psr/http-factory`, `psr/http-message`,
`psr/simple-cache`, `psr/log`, `psr/clock`) and `ramsey/uuid`. It does
**not** force Guzzle, Symfony HttpClient, or any serializer library on
your project.

## Installation

Install the library plus your preferred PSR-18 stack. The three common
options:

**With Guzzle** (recommended; Guzzle satisfies both PSR-18 and PSR-17):

```bash
composer require maviance/smobilpay-php-client guzzlehttp/guzzle
```

**With Symfony HttpClient + nyholm/psr7:**

```bash
composer require maviance/smobilpay-php-client symfony/http-client nyholm/psr7
```

**With php-http/curl-client + nyholm/psr7** (smallest footprint):

```bash
composer require maviance/smobilpay-php-client php-http/curl-client nyholm/psr7
```

## Quick start

The library exposes a single `SmobilpayClient` facade. Construct it once
per application with your partner credentials; the client lazily mints
and caches the OAuth 2.0 bearer token on first use.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Maviance\Smobilpay\SmobilpayClient;
use Maviance\Smobilpay\SmobilpayConfig;

$config = new SmobilpayConfig(
    baseUrl:   'https://api.example.invalid',       // issued during onboarding
    publicKey: getenv('SMOBILPAY_PUBLIC_KEY'),
    secretKey: getenv('SMOBILPAY_SECRET_KEY'),
);

$http    = new GuzzleClient(['timeout' => 30, 'connect_timeout' => 10]);
$factory = new HttpFactory();

$client = SmobilpayClient::create($config, $http, $factory, $factory);

$pong = $client->verify()->ping();
echo "Server time:    {$pong->time->format(DATE_ATOM)}\n";
echo "Server version: {$pong->version}\n";
```

## Authentication

The Smobilpay API uses **OAuth 2.0 `client_credentials`** exclusively.
Legacy HMAC request signing is **not** supported.

The client handles token issuance for you:

1. On the first authenticated request, the client POSTs
   `Basic base64(publicKey:secretKey)` to `{baseUrl}/oauth/token`
   with `grant_type=client_credentials`.
2. The returned JWT is cached in memory and attached as
   `Authorization: Bearer <jwt>` on every subsequent request.
3. The token is reused until it is within `tokenRefreshSkewSeconds` of
   expiry (default: 30s); then a fresh one is minted automatically.

To force a refresh (e.g. after a 401), call `$client->tokens()->refresh()`.

### Cross-request token caching

PHP-FPM workers don't share memory, so the in-memory cache only helps
within a single request. To amortize the token mint across requests on
shared hosting, pass any PSR-16 cache:

```php
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Psr16Cache;

$cache  = new Psr16Cache(new ApcuAdapter('smobilpay'));
$config = (new SmobilpayConfig(
    baseUrl: '...', publicKey: '...', secretKey: '...',
))->withTokenCache($cache);
```

A long-running PHP process (Laravel Octane, RoadRunner, Swoole,
or `php artisan`-style commands) gets the in-memory cache for free.

## Choosing the right flow

Every flow follows the same three-step shape — **discover → quote →
confirm** — and confirmation goes through a single endpoint
(`POST /v2/collectstd`) regardless of whether the payment item is a
cash-out (collection), a bill, a top-up, a voucher, a subscription, a
product, or a cash-in (disbursement).

What changes per flow is the masterdata call you use to discover the
right `payItemId`:

| Use case                       | Masterdata call                                | Item type        | Confirm call                       | Notes                                                            |
|--------------------------------|------------------------------------------------|------------------|------------------------------------|------------------------------------------------------------------|
| Collection (cash-out)          | `masterdata()->cashouts($serviceId)`           | `Cashout`        | `confirm()->collect($req)`         | Generic mobile-money collection. Money flows *out* of customer's wallet.|
| Bill payment                   | `initiate()->bills(...)`                       | `Bill`           | `confirm()->collect($req)`         | Bill is looked up by `serviceNumber`, not from static masterdata.|
| Airtime top-up                 | `masterdata()->topups($serviceId)`             | `Topup`          | `confirm()->collect($req)`         | Recipient phone goes on `customerPhonenumber` or `serviceNumber`.|
| Voucher purchase               | `masterdata()->vouchers($serviceId)`           | `Product`        | `confirm()->collect($req)`         | Code returned on `CollectionResponse->pin`.                      |
| Product purchase               | `masterdata()->products($serviceId)`           | `Product`        | `confirm()->collect($req)`         | Same shape as voucher but no PIN on the response.                |
| Subscription top-up (pay-TV …) | `initiate()->subscriptions(...)`               | `Subscription`   | `confirm()->collect($req)`         | Looked up by `serviceNumber` *or* `customerNumber`.              |
| Disbursement (cash-in)         | `masterdata()->cashins($serviceId)`            | `Cashin`         | `confirm()->collect($req)`         | Payout to recipient. Same `collect()` endpoint, item is a cash-in.|

For every item type the `payItemId` field is what flows into the quote
request. The `$service->isReq*` flags on the `Service` masterdata entry
tell you which optional `CollectionRequest` fields (customer name,
service number, customer number, …) become required for that service.

## Conventions

- All requests and responses are **JSON**.
- **Monetary amounts** on `QuoteRequest->amount` are integers in the
  local currency of the payment item (no decimals). Other amount fields
  on responses are floats per spec.
- **Currencies** are ISO 4217 codes (e.g. `XAF`, `EUR`).
- **Countries** are ISO 3166-1 alpha-3 codes (e.g. `CMR`).
- **Phone numbers** are E.164 without the leading `+` (e.g.
  `237699999999`).
- **Errors** raised by the API throw `SmobilpayApiException`. Match on
  `->error()->respCode` for programmatic handling — that is the
  canonical machine identifier per the partner spec.
- The `x-api-version: 3.0.0` header is attached on every secured
  request. Override via `$config->withApiVersion(...)` if you need a
  different protocol shape.

## Collection — cash-out

A `Cashout` item collects funds *out* of the customer's mobile wallet
into the partner's balance. This is the generic mobile-money collection
flow.

```php
use Maviance\Smobilpay\Model\CollectionRequest;
use Maviance\Smobilpay\Model\QuoteRequest;

// 1. Discover the cash-out items available for service 999999
$cashouts = $client->masterdata()->cashouts(serviceid: 999999);
$cashout  = $cashouts[0];

// 2. Request a quote (amounts are integers in local currency)
$quote = $client->initiate()->quote(
    new QuoteRequest(amount: 500, payItemId: $cashout->payItemId),
);

// 3. Confirm the collection
$request = new CollectionRequest(
    quoteId:              $quote->quoteId,
    customerPhonenumber:  '237699999999',
    customerEmailaddress: 'customer@example.com',
    serviceNumber:        '2371122334455',     // required when service.isReqServiceNumber
    trid:                 'ORDER-2026-05-02-0001', // optional caller-managed reference
    tag:                  'retail-front-desk',     // optional reporting tag (max 50 chars)
);

$response = $client->confirm()->collect($request);
echo "PTN:    {$response->ptn}\n";
echo "Status: {$response->status->value}\n"; // PENDING on x-api-version 3.0.0

// 4. Poll for final status by PTN
$statuses = $client->verify()->verifyTransaction(ptn: $response->ptn);
echo "Final status: {$statuses[0]->status->value}\n";
```

## Collection — bill payment

```php
$bills = $client->initiate()->bills('CDE', 4321, 'METER-001');
$bill  = $bills[0];

$quote = $client->initiate()->quote(
    new QuoteRequest(amount: (int) $bill->amountLocalCur, payItemId: $bill->payItemId),
);

$request = new CollectionRequest(
    quoteId:              $quote->quoteId,
    customerPhonenumber:  '237699999999',
    customerEmailaddress: 'customer@example.com',
    customerName:         'Jane Doe',        // required when service.isReqCustomerName
    serviceNumber:        'METER-001',
);

$response = $client->confirm()->collect($request);
```

## Collection — airtime top-up

```php
$topups = $client->masterdata()->topups(serviceid: $serviceId);
$topup  = $topups[0];

// FIXED-amount top-ups quote at the catalog price; CUSTOM-amount top-ups
// take any integer in the local currency.
$amount = $topup->amountLocalCur !== null ? (int) $topup->amountLocalCur : 500;

$quote = $client->initiate()->quote(
    new QuoteRequest(amount: $amount, payItemId: $topup->payItemId),
);

$response = $client->confirm()->collect(new CollectionRequest(
    quoteId:              $quote->quoteId,
    customerPhonenumber:  '237699999999',
    customerEmailaddress: 'customer@example.com',
    serviceNumber:        '237699999999',     // recipient MSISDN
));
```

## Collection — voucher purchase

For services of type `VOUCHER` the digital code is delivered on
`CollectionResponse->pin` once the collection succeeds.

```php
$vouchers = $client->masterdata()->vouchers(serviceid: $serviceId);
$voucher  = $vouchers[0];

$quote = $client->initiate()->quote(
    new QuoteRequest(amount: (int) $voucher->amountLocalCur, payItemId: $voucher->payItemId),
);

$response = $client->confirm()->collect(new CollectionRequest(
    quoteId:              $quote->quoteId,
    customerPhonenumber:  $customerPhone,
    customerEmailaddress: $customerEmail,
));

$redemptionPin = $response->pin;
```

## Collection — product purchase

Generic products work like vouchers but do not return a redemption PIN:

```php
$products = $client->masterdata()->products(serviceid: $serviceId);
$product  = $products[0];

$quote = $client->initiate()->quote(
    new QuoteRequest(amount: (int) $product->amountLocalCur, payItemId: $product->payItemId),
);

$response = $client->confirm()->collect(new CollectionRequest(
    quoteId:              $quote->quoteId,
    customerPhonenumber:  $customerPhone,
    customerEmailaddress: $customerEmail,
));
```

## Collection — subscription top-up

Subscriptions (e.g. pay-TV like Canal+) are looked up by *either*
`serviceNumber` *or* `customerNumber` — pass one and leave the other
`null`. The returned list may contain several `Subscription` items
representing different renewal options for the same customer; pick one
and quote against its `payItemId`.

```php
$subs = $client->initiate()->subscriptions(
    'CANALPLUS',
    4321,
    serviceNumber:  'DECODER-001234',
    customerNumber: null,
);
$sub = $subs[0];

$quote = $client->initiate()->quote(
    new QuoteRequest(amount: (int) $sub->amountLocalCur, payItemId: $sub->payItemId),
);

$response = $client->confirm()->collect(new CollectionRequest(
    quoteId:              $quote->quoteId,
    customerPhonenumber:  '237699999999',
    customerEmailaddress: 'customer@example.com',
    customerName:         $sub->customerName,
    serviceNumber:        'DECODER-001234',
));
```

## Disbursement — cash-in

A `Cashin` item pays funds *into* a recipient's mobile wallet from the
partner's balance. It goes through the same `/v2/collectstd` endpoint
as collections.

```php
$cashins = $client->masterdata()->cashins(serviceid: $serviceId);
$cashin  = $cashins[0];

$quote = $client->initiate()->quote(
    new QuoteRequest(amount: 10000, payItemId: $cashin->payItemId),
);

$response = $client->confirm()->collect(new CollectionRequest(
    quoteId:              $quote->quoteId,
    customerPhonenumber:  '237699999999',      // recipient phone
    customerEmailaddress: 'recipient@example.com',
    serviceNumber:        '237699999999',      // recipient MSISDN
    trid:                 'PAYOUT-2026-05-02-0001',
));
```

## Pre-payment verification

For services that report `isVerifiable: true`, you can verify a service
number before quoting:

```php
$valid = $client->accountValidation()->verifyServiceNumber('ENEO', 1234, '01234567');
```

For partners enabled for the restricted **`/v2/validate`** endpoint, a
richer account-lookup is available — it returns a tri-state status
plus the customer name where available:

```php
use Maviance\Smobilpay\Model\CustomerAccount\Status;

$account = $client->accountValidation()->validateAccount('237699999999', 999999);

switch ($account->status) {
    case Status::VERIFIED:  /* cross-checked against the provider */    break;
    case Status::VALIDATED: /* syntactically valid (e.g. regex match) */ break;
    case Status::UNKNOWN:   /* neither verified nor validated */         break;
}
```

`/v2/validate` is gated behind compliance/KYC review — unauthorized
callers receive HTTP 401 as `SmobilpayApiException`. Contact your
integration manager to request enablement.

## Catalog discovery

Most integrations cache the catalog and refresh it on a schedule:

```php
$merchants = $client->masterdata()->merchants();
$services  = $client->masterdata()->services();
```

The `Service` record tells you which flow applies (cash-out, bill,
top-up, voucher, product, subscription, cash-in) via its `$type` and
which optional `CollectionRequest` fields the merchant requires via the
`$isReq*` boolean flags.

## Account and ping utilities

```php
// Liveness check + protocol/version handshake.
$pong = $client->verify()->ping();

// Aggregator-level account info: balance, currency, status.
$account = $client->verify()->account();
```

## Historical lookups

Search by **exactly one** of:

```php
$client->verify()->historyByPtn('PTN-202605020800001');
$client->verify()->historyByTrid('ORDER-2026-05-02-0001');
$client->verify()->historyByDateRange(
    new DateTimeImmutable('2026-05-01'),
    new DateTimeImmutable('2026-05-31'),
);
```

Combinations are rejected by the server with an error envelope.

## Error handling

```php
use Maviance\Smobilpay\Exception\SmobilpayApiException;
use Maviance\Smobilpay\Exception\SmobilpayAuthException;
use Maviance\Smobilpay\Exception\SmobilpayTransportException;

try {
    $quote = $client->initiate()->quote($request);
} catch (SmobilpayAuthException $e) {
    // OAuth token issuance failed — bad credentials, etc.
    logger()->error('Auth failed', [
        'status'     => $e->httpStatus(),
        'oauthError' => $e->oauthError(),
    ]);
} catch (SmobilpayApiException $e) {
    // API returned a non-2xx with the standard Error envelope.
    if ($e->error() !== null) {
        logger()->error('API error', [
            'respCode' => $e->error()->respCode,
            'devMsg'   => $e->error()->devMsg,
            'link'     => $e->error()->link,
        ]);
    }
    if ($e->httpStatus() === 498) {
        // Quote expired — re-quote and retry.
    }
} catch (SmobilpayTransportException $e) {
    // Network failure, DNS, TLS, timeout.
    logger()->warning('Transport error', ['error' => $e->getMessage()]);
}
```

The full Smobilpay error catalog (the `respCode` → meaning mapping) is
delivered to partners during onboarding.

## Configuration reference

| Option                       | Default          | Notes                                                                                          |
|------------------------------|------------------|------------------------------------------------------------------------------------------------|
| `baseUrl`                    | required         | Issued during onboarding                                                                       |
| `publicKey`                  | required         | Partner OAuth client identifier                                                                |
| `secretKey`                  | required         | Partner OAuth client secret                                                                    |
| `apiVersion`                 | `3.0.0`          | Value sent as `x-api-version` header                                                           |
| `requestTimeoutSeconds`      | `30`             | Surfaced for convenience — set the actual timeout on your PSR-18 client (Guzzle, Symfony, …)  |
| `tokenRefreshSkewSeconds`    | `30`             | Mint a fresh token this many seconds ahead of expiry                                           |
| `httpClient` (PSR-18)        | passed at `create()` | Construct via `SmobilpayClient::create($config, $http, $factory, $factory)`               |
| `requestFactory` (PSR-17)    | passed at `create()` |                                                                                            |
| `streamFactory` (PSR-17)     | passed at `create()` |                                                                                            |
| `tokenCache` (PSR-16)        | `null`           | Optional — persists the OAuth token across PHP requests                                        |
| `logger` (PSR-3)             | `null`           | Optional — logs request/response metadata at `debug`                                           |
| `clock` (PSR-20)             | internal UTC clock | Override for tests                                                                          |

`SmobilpayConfig` is `readonly`. Use the `withTokenCache()`,
`withLogger()`, `withClock()`, `withApiVersion()`, or `withHttp()`
methods to derive an immutable copy.

## Smoke test

The library ships a `bin/smoke-test` harness that exercises every flow
against a real partner environment. It reads the same `smoke-test.json`
config that the Java client reads, runs the same 15 scenarios in the
same order, and emits the same `RUN`/`PASS`/`SKIP`/`FAIL` output —
so the two runs can be diff-compared:

```bash
cp smoke-test.example.json smoke-test.json
# edit smoke-test.json to fill in baseUrl, publicKey, secretKey, and the
# per-flow blocks you want exercised

composer smoke                      # online run
composer smoke -- --strip-volatile  # online + scrub timestamps/UUIDs for diff
composer smoke -- --offline         # replay fixtures, no network
```

Compare against Java side-by-side:

```bash
export SMOBILPAY_SMOKE_CONFIG=$PWD/smoke-test.json
( cd ../java && ./gradlew runSmokeTest --console=plain -q ) > /tmp/java.log
composer smoke -- --strip-volatile > /tmp/php.log
diff -u /tmp/java.log /tmp/php.log
```

The smoke test is **read-only / quote-only**; it never calls
`/v2/collectstd`, so it does not move money.

## Onboarding

Base URL, partner credentials (`publicKey` / `secretKey`), callback URL
registration, and the full error catalog are issued by Maviance support
during partner onboarding. They are intentionally not published in the
spec or this README. Contact **support@smobilpay.com**.

## Development

```bash
composer install
composer test          # run PHPUnit
composer test:coverage # tests + coverage report (needs Xdebug or PCOV)
composer lint          # PSR-12 dry run
composer lint:fix      # apply PSR-12 fixes
composer stan          # PHPStan level 8
composer check         # lint + stan + test (local pre-commit gate)
composer smoke         # run the smoke-test harness (see above)
```

CI exercises the matrix PHP 8.2 / 8.3 / 8.4 × `lowest` / `highest`
Composer deps. The coverage gate fails the build below 80% line coverage
on `src/` (excluding `src/Model/`).

## License

Apache-2.0. See [LICENSE](LICENSE).
