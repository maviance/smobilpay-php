# Upgrading from `maviance/smobilpay-php` v2.x → `maviance/smobilpay-php-client` v3.x

This is a hard break — different package name, different namespace,
OAuth 2.0 instead of HMAC. The packages can coexist in your
`composer.json` during the migration window.

## 1. Get OAuth 2.0 credentials

Contact **support@smobilpay.com** for partner re-onboarding. You will
receive a `publicKey` / `secretKey` pair that replaces your old HMAC
`token` / `secret`. The S3P API server no longer accepts HMAC
signatures, so this step is required before any v3.x code can connect.

## 2. Update composer.json

```diff
 {
   "require": {
-    "maviance/smobilpay-php": "^2.2",
+    "maviance/smobilpay-php-client": "^3.2",
+    "guzzlehttp/guzzle": "^7.5"
   }
 }
```

The v3 client does **not** force a specific HTTP stack on you — pick
one of:

- **Guzzle** (suggested default; provides both PSR-18 and PSR-17)
- `symfony/http-client` + `nyholm/psr7`
- `php-http/curl-client` + `nyholm/psr7`

```bash
composer require maviance/smobilpay-php-client guzzlehttp/guzzle
composer remove  maviance/smobilpay-php   # once all callers are migrated
```

You can `composer require` both packages simultaneously while you
migrate call sites one by one — there is no namespace collision.

## 3. Rewire client construction

### Before (v2)

```php
use Maviance\S3PApiClient\ApiClient;
use Maviance\S3PApiClient\Configuration;
use Maviance\S3PApiClient\Service\AccountApi;

$token = $_ENV['S3P_TOKEN'];
$secret = $_ENV['S3P_SECRET'];

$config = new Configuration();
$config->setHost('https://api.example.invalid/v2');
$client = new ApiClient($token, $secret, ['verify' => false]);

$accountApi = new AccountApi($client, $config);
$account = $accountApi->accountGet('3.0.0');
```

### After (v3)

```php
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Maviance\Smobilpay\SmobilpayClient;
use Maviance\Smobilpay\SmobilpayConfig;

$config = new SmobilpayConfig(
    baseUrl:   'https://api.example.invalid',  // no /v2 suffix — added per-call
    publicKey: $_ENV['SMOBILPAY_PUBLIC_KEY'],
    secretKey: $_ENV['SMOBILPAY_SECRET_KEY'],
);

$http    = new GuzzleClient(['timeout' => 30]);
$factory = new HttpFactory();
$client  = SmobilpayClient::create($config, $http, $factory, $factory);

$account = $client->verify()->account();
```

Notes:

- The `baseUrl` no longer includes `/v2` — the client adds `/v2/...`
  per endpoint.
- The `x-api-version: 3.0.0` header is sent automatically on every
  call. Override via `$config->withApiVersion(...)` if you must.
- The HMAC-era `'verify' => false` Guzzle hack should be a temporary
  measure if your CA bundle is misconfigured — fix the CA bundle in
  production instead of disabling TLS verification.

## 4. Method-name mapping

| v2 (auto-generated)                                  | v3 (curated)                                                |
|------------------------------------------------------|-------------------------------------------------------------|
| `AccountApi::accountGet($apiVersion)`                | `$client->verify()->account()`                              |
| `HealthcheckApi::pingGet($apiVersion)`               | `$client->verify()->ping()`                                 |
| `AccountValidationApi::verifyGet($v, $m, $sid, $sn)` | `$client->accountValidation()->verifyServiceNumber($m, $sid, $sn)` |
| *(new)*                                              | `$client->accountValidation()->validateAccount($dest, $sid)`|
| `MasterdataApi::merchantGet($apiVersion)`            | `$client->masterdata()->merchants()`                        |
| `MasterdataApi::serviceGet($apiVersion)`             | `$client->masterdata()->services()`                         |
| `MasterdataApi::serviceIdGet($v, $id)`               | `array_values(array_filter($client->masterdata()->services(), fn($s) => $s->serviceid === $id))[0] ?? null` |
| `MasterdataApi::productGet($v, $sid)`                | `$client->masterdata()->products($sid)`                     |
| `MasterdataApi::voucherGet($v, $sid)`                | `$client->masterdata()->vouchers($sid)`                     |
| `MasterdataApi::topupGet($v, $sid)`                  | `$client->masterdata()->topups($sid)`                       |
| `MasterdataApi::cashinGet($v, $sid)`                 | `$client->masterdata()->cashins($sid)`                      |
| `MasterdataApi::cashoutGet($v, $sid)`                | `$client->masterdata()->cashouts($sid)`                     |
| `InitiateApi::billGet($v, $m, $sid, $sn)`            | `$client->initiate()->bills($m, $sid, $sn)`                 |
| `InitiateApi::subscriptionGet($v, $m, $sid, ...)`    | `$client->initiate()->subscriptions($m, $sid, $sn, $cn)`    |
| `InitiateApi::quotestdPost($v, $body)`               | `$client->initiate()->quote(new QuoteRequest(...))`         |
| `ConfirmApi::collectstdPost($v, $body)`              | `$client->confirm()->collect(new CollectionRequest(...))`   |
| `VerifyApi::verifytxGet($v, $ptn, $trid)`            | `$client->verify()->verifyTransaction($ptn, $trid)`         |
| `VerifyApi::historystdGet($v, $ptn, $trid, $from, $to)` | `$client->verify()->historyByPtn(...)` / `historyByTrid(...)` / `historyByDateRange(...)` |

## 5. Models

All v2 generated model classes (`Maviance\S3PApiClient\Model\*`) are
removed. Equivalents under `Maviance\Smobilpay\Model\*` are
`readonly` classes; access fields as **public properties**, not
`getXxx()` methods:

```php
// v2
$bill->getAmountLocalCur();

// v3
$bill->amountLocalCur;
```

The following are new in v3:

- `CustomerAccount` + `CustomerAccount\Status` (UNKNOWN/VALIDATED/VERIFIED)
  for the restricted `/v2/validate` endpoint.
- `PaymentItem` marker interface — implemented by `Cashin`, `Cashout`,
  `Topup`, `Product`, `Bill`, `Subscription`.
- All enums are now native PHP `enum`s rather than string constants.

## 6. Error handling

v2 throws `Maviance\S3PApiClient\ApiException` for everything. v3 has
a structured hierarchy:

| Situation                            | v3 exception                       |
|--------------------------------------|------------------------------------|
| Bad credentials, OAuth mint failure  | `SmobilpayAuthException`           |
| API returned non-2xx                 | `SmobilpayApiException`            |
| Network/DNS/TLS/timeout              | `SmobilpayTransportException`      |
| Server sent unparseable JSON         | `SmobilpayParseException`          |
| Caller passed invalid arguments      | `SmobilpayConfigException` (extends `\InvalidArgumentException`) |

```php
try {
    $client->confirm()->collect($request);
} catch (SmobilpayApiException $e) {
    if ($e->httpStatus() === 498) {
        // Quote expired — re-quote and retry
    }
    $respCode = $e->error()?->respCode;  // canonical machine identifier
}
```

## 7. Cross-request token caching (optional)

PHP-FPM doesn't share memory across requests, so without intervention
every web request mints a fresh OAuth token (~50–200 ms overhead).
Pass any PSR-16 cache to amortize that cost:

```php
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Psr16Cache;

$config = (new SmobilpayConfig(...))
    ->withTokenCache(new Psr16Cache(new ApcuAdapter('smobilpay')));
```

Long-running PHP (Octane, RoadRunner, Swoole, console commands) gets
the in-memory cache for free.

## 8. Verify with the smoke test

```bash
cp smoke-test.example.json smoke-test.json
# edit smoke-test.json
composer smoke
```

The smoke test is **read-only / quote-only** (never calls
`/v2/collectstd`), so it is safe to run against production.
