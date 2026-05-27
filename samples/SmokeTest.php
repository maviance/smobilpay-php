<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Samples;

use DateTimeImmutable;
use DateTimeZone;
use Http\Mock\Client as MockHttpClient;
use Maviance\Smobilpay\Exception\SmobilpayApiException;
use Maviance\Smobilpay\Exception\SmobilpayAuthException;
use Maviance\Smobilpay\Model\PaymentItem;
use Maviance\Smobilpay\Model\QuoteRequest;
use Maviance\Smobilpay\Model\ServiceType;
use Maviance\Smobilpay\SmobilpayClient;
use Maviance\Smobilpay\SmobilpayConfig;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Throwable;

/**
 * Smoke-test harness for the Smobilpay client against a real partner
 * environment.
 *
 * Read-only / quote-only — never calls `/v2/collectstd`, so it does not
 * move money. Output format mirrors the Java client's `runSmokeTest`
 * line-for-line so the two runs can be diffed.
 *
 * Path resolution: arg[0] → `SMOBILPAY_SMOKE_CONFIG` env → `./smoke-test.json`
 * in CWD (identical to Java).
 *
 * Flags:
 *  - `--offline`         Use fixture responses instead of real HTTP (zero
 *                        network). Useful as a self-documenting demo.
 *  - `--strip-volatile`  Scrub timestamp / JWT-prefix / UUID / PTN lines
 *                        from the output so the result diffs cleanly
 *                        against Java's run.
 *
 * Exit codes: 0 on all passed/skipped, 1 on any failure, 2 on config error.
 */
final class SmokeTest
{
    private const SEP = '----------------------------------------------------------------------';

    private int $passed = 0;
    private int $failed = 0;
    private int $skipped = 0;
    private bool $stripVolatile = false;
    private bool $offline = false;

    public function __construct(private readonly SmokeTestConfig $cfg)
    {
    }

    /**
     * @param list<string> $argv
     */
    public static function main(array $argv): int
    {
        $pathArg = null;
        $flags = [];
        // Skip $argv[0] (script name); collect flags and the first positional arg.
        for ($i = 1; $i < \count($argv); $i++) {
            $a = $argv[$i];
            if (\str_starts_with($a, '--')) {
                $flags[] = $a;
            } elseif ($pathArg === null) {
                $pathArg = $a;
            }
        }
        if (\in_array('--help', $flags, true) || \in_array('-h', $flags, true)) {
            self::printHelp();

            return 0;
        }

        $path = self::resolveConfigPath($pathArg);
        try {
            $cfg = SmokeTestConfig::fromJsonFile($path);
        } catch (SmokeTestConfigException $e) {
            \fwrite(STDERR, 'Configuration error: ' . $e->getMessage() . PHP_EOL);

            return 2;
        }

        $harness = new self($cfg);
        $harness->stripVolatile = \in_array('--strip-volatile', $flags, true);
        $harness->offline = \in_array('--offline', $flags, true);

        return $harness->execute();
    }

    public function execute(): int
    {
        [$http, $factory] = $this->buildHttpClient();
        $config = $this->buildSmobilpayConfig();
        $client = SmobilpayClient::create($config, $http, $factory, $factory);

        $banner = \sprintf(
            'Smobilpay smoke test  —  baseUrl=%s, apiVersion=%s, publicKey=%s%s',
            $this->redactBaseUrl($config->baseUrl),
            $config->apiVersion,
            $this->redactKey($config->publicKey),
            $this->offline ? ' [OFFLINE]' : '',
        );
        $this->banner($banner);

        $this->scenarioPing($client);
        $this->scenarioTokenRefresh($client);
        $this->scenarioAccount($client);
        $this->scenarioMerchants($client);
        $this->scenarioServices($client);
        $this->scenarioCashout($client);
        $this->scenarioBill($client);
        $this->scenarioTopup($client);
        $this->scenarioVoucher($client);
        $this->scenarioProduct($client);
        $this->scenarioSubscription($client);
        $this->scenarioCashin($client);
        $this->scenarioVerifyServiceNumber($client);
        $this->scenarioValidateAccount($client);
        $this->scenarioHistoryLast7Days($client);

        $this->printSummary();

        return $this->failed === 0 ? 0 : 1;
    }

    // --- Scenarios -------------------------------------------------------

    private function scenarioPing(SmobilpayClient $client): void
    {
        $this->run('Ping (auth probe)', function () use ($client): void {
            $pong = $client->verify()->ping();
            $this->detail('server time:    ' . $pong->time->format(DATE_ATOM));
            $this->detail('server version: ' . $pong->version);
            $this->detail('nonce echo:     ' . ($pong->nonce ?? '<null>'));
            $this->detail('public key:     ' . $pong->key);
        });
    }

    private function scenarioTokenRefresh(SmobilpayClient $client): void
    {
        $this->run('OAuth 2.0 token refresh', function () use ($client): void {
            $first = $client->tokens()->accessToken();
            $forced = $client->tokens()->refresh();
            if ($forced === '') {
                throw new \RuntimeException('refresh returned empty token');
            }
            $client->verify()->ping();
            $this->detail('first  bearer prefix: ' . $this->jwtPrefix($first) . '...');
            $this->detail('forced bearer prefix: ' . $this->jwtPrefix($forced) . '...');
            $this->detail('identical: ' . ($first === $forced ? 'true' : 'false'));
        });
    }

    private function scenarioAccount(SmobilpayClient $client): void
    {
        $this->run('Account profile', function () use ($client): void {
            $a = $client->verify()->account();
            $this->detail("agent:           {$a->agentName} (id={$a->agentId})");
            $this->detail("company:         {$a->companyName}");
            $this->detail("balance:         {$a->balance} {$a->currency}");
            $this->detail("daily limit max: {$a->limitMax}");
            $this->detail("limit remaining: {$a->limitRemaining}");
        });
    }

    private function scenarioMerchants(SmobilpayClient $client): void
    {
        $this->run('Merchant catalog', function () use ($client): void {
            $merchants = $client->masterdata()->merchants();
            $this->detail('merchants: ' . \count($merchants));
            $sample = \min(5, \count($merchants));
            for ($i = 0; $i < $sample; $i++) {
                $m = $merchants[$i];
                $this->detail("  - {$m->merchant} : {$m->name} ({$m->country}, {$m->status->value})");
            }
            if (\count($merchants) > $sample) {
                $this->detail('  ...and ' . (\count($merchants) - $sample) . ' more');
            }
        });
    }

    private function scenarioServices(SmobilpayClient $client): void
    {
        $this->run('Service catalog', function () use ($client): void {
            $services = $client->masterdata()->services();
            $this->detail('services: ' . \count($services));

            $byType = [];
            foreach ($services as $s) {
                $byType[$s->type->value] = ($byType[$s->type->value] ?? 0) + 1;
            }
            \ksort($byType);
            $this->detail('distribution by type:');
            foreach ($byType as $t => $c) {
                $this->detail("  - {$t}: {$c}");
            }
            $this->listServicesOfType($services, ServiceType::VOUCHER, 'VOUCHER services');
            $this->listServicesOfType($services, ServiceType::SUBSCRIPTION, 'SUBSCRIPTION services');
            $verifiable = \array_values(\array_filter($services, fn ($s) => $s->isVerifiable));
            if ($verifiable !== []) {
                $this->detail("verifiable services (isVerifiable=true) — candidates for the 'verify' block:");
                foreach ($verifiable as $s) {
                    $this->detail(\sprintf('  - serviceId=%d merchant=%s title=%s',
                        $s->serviceid, $s->merchant, $s->title));
                }
            }
        });
    }

    /**
     * @param list<\Maviance\Smobilpay\Model\Service> $services
     */
    private function listServicesOfType(array $services, ServiceType $type, string $label): void
    {
        $matches = \array_values(\array_filter($services, fn ($s) => $s->type === $type));
        if ($matches === []) {
            return;
        }
        $this->detail($label . ':');
        foreach ($matches as $s) {
            $this->detail(\sprintf('  - serviceId=%d merchant=%s title=%s',
                $s->serviceid, $s->merchant, $s->title));
        }
    }

    private function scenarioCashout(SmobilpayClient $client): void
    {
        $this->run('Collection — cash-out (discover + quote)', function () use ($client): void {
            $c = $this->cfg->cashout;
            if ($c === null) {
                $this->skip("no 'cashout' block in config");
            }
            $items = $client->masterdata()->cashouts(serviceid: $c['serviceId']);
            if ($items === []) {
                throw new \RuntimeException('no cashout items for serviceId=' . $c['serviceId']);
            }
            $item = $items[0];
            $this->detail(\sprintf('picked: %s (%s, %s, local=%s %s)',
                $item->payItemId, $item->name ?? '', $item->amountType->value,
                $item->amountLocalCur ?? 'null', $item->localCur));
            $this->quoteAndReport($client, $item, $c['amount']);
        });
    }

    private function scenarioBill(SmobilpayClient $client): void
    {
        $this->run('Collection — bill payment (discover + quote)', function () use ($client): void {
            $c = $this->cfg->bill;
            if ($c === null) {
                $this->skip("no 'bill' block in config");
            }
            $bills = $client->initiate()->bills($c['merchant'], $c['serviceId'], $c['serviceNumber']);
            if ($bills === []) {
                throw new \RuntimeException(\sprintf(
                    'no bills for %s/%d/%s',
                    $c['merchant'], $c['serviceId'], $c['serviceNumber'],
                ));
            }
            $bill = $bills[0];
            $this->detail(\sprintf('picked: %s (%s, amount=%s %s, due=%s)',
                $bill->payItemId, $bill->billType?->value ?? 'null',
                $bill->amountLocalCur ?? 'null', $bill->localCur,
                $bill->billDueDate?->format('Y-m-d') ?? 'null'));
            $amount = (int) ($bill->amountLocalCur ?? 0);
            $this->quoteAndReport($client, $bill, $amount);
        });
    }

    private function scenarioTopup(SmobilpayClient $client): void
    {
        $this->run('Collection — airtime top-up (discover + quote)', function () use ($client): void {
            $c = $this->cfg->topup;
            if ($c === null) {
                $this->skip("no 'topup' block in config");
            }
            $items = $client->masterdata()->topups(serviceid: $c['serviceId']);
            if ($items === []) {
                throw new \RuntimeException('no topup items for serviceId=' . $c['serviceId']);
            }
            $item = $items[0];
            $this->detail(\sprintf('picked: %s (%s, %s, local=%s %s)',
                $item->payItemId, $item->name ?? '', $item->amountType->value,
                $item->amountLocalCur ?? 'null', $item->localCur));
            $this->quoteAndReport($client, $item, $c['amount']);
        });
    }

    private function scenarioVoucher(SmobilpayClient $client): void
    {
        $this->run('Collection — voucher purchase (discover + quote)', function () use ($client): void {
            $c = $this->cfg->voucher;
            if ($c === null) {
                $this->skip("no 'voucher' block in config");
            }
            try {
                $items = $client->masterdata()->vouchers(serviceid: $c['serviceId']);
            } catch (SmobilpayApiException $e) {
                if ($e->error()?->respCode === 41004) {
                    $this->skip('/v2/voucher rejects serviceId=' . $c['serviceId']
                        . ' (respCode 41004) even though the catalog labels it VOUCHER');
                }
                throw $e;
            }
            if ($items === []) {
                throw new \RuntimeException('no vouchers for serviceId=' . $c['serviceId']);
            }
            $item = $items[0];
            $this->detail(\sprintf('picked: %s (%s, %s, local=%s %s)',
                $item->payItemId, $item->name ?? '', $item->amountType->value,
                $item->amountLocalCur ?? 'null', $item->localCur));
            $this->quoteAndReport($client, $item, $this->resolveAmount($item, $c['amount'] ?? null));
        });
    }

    private function scenarioProduct(SmobilpayClient $client): void
    {
        $this->run('Collection — product purchase (discover + quote)', function () use ($client): void {
            $c = $this->cfg->product;
            if ($c === null) {
                $this->skip("no 'product' block in config");
            }
            $items = $client->masterdata()->products(serviceid: $c['serviceId']);
            if ($items === []) {
                throw new \RuntimeException('no products for serviceId=' . $c['serviceId']);
            }
            $item = $items[0];
            $this->detail(\sprintf('picked: %s (%s, %s, local=%s %s)',
                $item->payItemId, $item->name ?? '', $item->amountType->value,
                $item->amountLocalCur ?? 'null', $item->localCur));
            $this->quoteAndReport($client, $item, $this->resolveAmount($item, $c['amount'] ?? null));
        });
    }

    private function scenarioSubscription(SmobilpayClient $client): void
    {
        $this->run('Collection — subscription top-up (discover + quote)', function () use ($client): void {
            $c = $this->cfg->subscription;
            if ($c === null) {
                $this->skip("no 'subscription' block in config");
            }
            if (($c['serviceNumber'] ?? null) === null && ($c['customerNumber'] ?? null) === null) {
                $this->skip("subscription block needs either 'serviceNumber' or 'customerNumber'");
            }
            $subs = $client->initiate()->subscriptions(
                $c['merchant'],
                $c['serviceId'],
                $c['serviceNumber'] ?? null,
                $c['customerNumber'] ?? null,
            );
            if ($subs === []) {
                throw new \RuntimeException('no subscriptions for ' . $c['merchant'] . '/' . $c['serviceId']);
            }
            $sub = $subs[0];
            $this->detail(\sprintf('picked: %s (%s, customer=%s, amount=%s %s, due=%s)',
                $sub->payItemId, $sub->name ?? '', $sub->customerName ?? 'null',
                $sub->amountLocalCur ?? 'null', $sub->localCur,
                $sub->dueDate?->format('Y-m-d') ?? 'null'));
            $this->quoteAndReport($client, $sub, $this->resolveAmount($sub, $c['amount'] ?? null));
        });
    }

    private function scenarioCashin(SmobilpayClient $client): void
    {
        $this->run('Disbursement — cash-in (discover + quote)', function () use ($client): void {
            $c = $this->cfg->cashin;
            if ($c === null) {
                $this->skip("no 'cashin' block in config");
            }
            $items = $client->masterdata()->cashins(serviceid: $c['serviceId']);
            if ($items === []) {
                throw new \RuntimeException('no cashin items for serviceId=' . $c['serviceId']);
            }
            $item = $items[0];
            $this->detail(\sprintf('picked: %s (%s, %s, local=%s %s)',
                $item->payItemId, $item->name ?? '', $item->amountType->value,
                $item->amountLocalCur ?? 'null', $item->localCur));
            $this->quoteAndReport($client, $item, $c['amount']);
        });
    }

    private function scenarioVerifyServiceNumber(SmobilpayClient $client): void
    {
        $this->run('Account validation — verify serviceNumber', function () use ($client): void {
            $c = $this->cfg->verify;
            if ($c === null) {
                $this->skip("no 'verify' block in config");
            }
            try {
                $valid = $client->accountValidation()->verifyServiceNumber(
                    $c['merchant'], $c['serviceId'], $c['serviceNumber'],
                );
                $this->detail(\sprintf('%s for %s/%d -> %s',
                    $c['serviceNumber'], $c['merchant'], $c['serviceId'],
                    $valid ? 'valid' : 'invalid'));
            } catch (SmobilpayApiException $e) {
                if ($e->error()?->respCode === 40408) {
                    $this->skip("service {$c['merchant']}/{$c['serviceId']} does not support pre-payment verification (respCode 40408)");
                }
                throw $e;
            }
        });
    }

    private function scenarioValidateAccount(SmobilpayClient $client): void
    {
        $this->run('Account validation — validate destination', function () use ($client): void {
            $c = $this->cfg->validate;
            if ($c === null) {
                $this->skip("no 'validate' block in config");
            }
            try {
                $a = $client->accountValidation()->validateAccount($c['destination'], $c['serviceId']);
                $this->detail('destination: ' . $a->destination);
                $this->detail('status:      ' . $a->status->value);
                $this->detail('name:        ' . ($a->name ?? '<null>'));
            } catch (SmobilpayApiException $e) {
                if ($e->httpStatus() === 401) {
                    $this->skip('GET /v2/validate is a restricted endpoint and is not enabled'
                        . ' for this partner (HTTP 401). Compliance review is required —'
                        . ' contact your Maviance integration manager.');
                }
                throw $e;
            }
        });
    }

    private function scenarioHistoryLast7Days(SmobilpayClient $client): void
    {
        $this->run('History - last 7 days', function () use ($client): void {
            $today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
            $weekAgo = $today->modify('-7 days');
            $rows = $client->verify()->historyByDateRange($weekAgo, $today);
            $this->detail("range:        {$weekAgo->format('Y-m-d')} -> {$today->format('Y-m-d')}");
            $this->detail('transactions: ' . \count($rows));
            $sample = \min(3, \count($rows));
            for ($i = 0; $i < $sample; $i++) {
                $s = $rows[$i];
                $this->detail(\sprintf('  - %s : %s, %s %s, trid=%s',
                    $s->ptn, $s->status->value,
                    $s->priceLocalCur ?? 'null', $s->localCur ?? '',
                    $s->trid ?? 'null'));
            }
        });
    }

    // --- Helpers ---------------------------------------------------------

    private function quoteAndReport(SmobilpayClient $client, PaymentItem $item, int $amount): void
    {
        if ($amount < 1) {
            throw new \RuntimeException(
                'cannot quote with amount=' . $amount . ' — set "amount" in this block of smoke-test.json',
            );
        }
        $payItemId = $item->payItemId;
        \assert(\is_string($payItemId));
        $quote = $client->initiate()->quote(new QuoteRequest($amount, $payItemId));
        $this->detail('quoteId:        ' . $quote->quoteId->toString());
        $this->detail('expiresAt:      ' . $quote->expiresAt->format(DATE_ATOM));
        $this->detail("price (local):  {$quote->priceLocalCur} {$quote->localCur}");
        $this->detail("price (system): {$quote->priceSystemCur} {$quote->systemCur}");
        $this->detail('promotion:      ' . ($quote->promotion ?? '<null>'));
        $this->detail('(intentionally NOT calling /v2/collectstd)');
    }

    private function resolveAmount(PaymentItem $item, ?int $configAmount): int
    {
        if ($configAmount !== null && $configAmount > 0) {
            return $configAmount;
        }
        // PaymentItem implementors all expose ->amountLocalCur publicly via the marker interface.
        /** @var float|null $local */
        $local = $item->amountLocalCur ?? null;
        if ($local !== null && $local >= 1.0) {
            return (int) $local;
        }
        throw new \RuntimeException(\sprintf(
            'item %s has no fixed catalog amount. Set "amount" in this block of smoke-test.json.',
            $item->payItemId ?? '?',
        ));
    }

    private function run(string $name, callable $scenario): void
    {
        echo self::SEP . PHP_EOL;
        echo 'RUN  ' . $name . PHP_EOL;
        try {
            $scenario();
            $this->passed++;
            echo 'PASS ' . $name . PHP_EOL;
        } catch (SkipScenario $e) {
            $this->skipped++;
            echo 'SKIP ' . $name . ' - ' . $e->getMessage() . PHP_EOL;
        } catch (SmobilpayAuthException $e) {
            $this->failed++;
            echo 'FAIL ' . $name . ' - auth error (HTTP ' . $e->httpStatus()
                . ($e->oauthError() !== null ? ', error=' . $e->oauthError() : '')
                . '): ' . $e->getMessage() . PHP_EOL;
        } catch (SmobilpayApiException $e) {
            $this->failed++;
            echo 'FAIL ' . $name . ' - API error (HTTP ' . $e->httpStatus() . ')' . PHP_EOL;
            $err = $e->error();
            if ($err !== null) {
                $this->detail('respCode: ' . $err->respCode);
                $this->detail('devMsg:   ' . ($err->devMsg ?? '<null>'));
                if ($err->usrMsg !== null) {
                    $this->detail('usrMsg:   ' . $err->usrMsg);
                }
                if ($err->link !== null) {
                    $this->detail('link:     ' . $err->link);
                }
            }
        } catch (Throwable $e) {
            $this->failed++;
            echo 'FAIL ' . $name . ' - ' . (new \ReflectionClass($e))->getShortName()
                . ': ' . $e->getMessage() . PHP_EOL;
        }
    }

    private function detail(string $line): void
    {
        if ($this->stripVolatile) {
            $line = $this->stripVolatileFields($line);
        }
        echo '     ' . $line . PHP_EOL;
    }

    private function banner(string $message): void
    {
        echo PHP_EOL;
        echo '======================================================================' . PHP_EOL;
        echo $message . PHP_EOL;
        echo '======================================================================' . PHP_EOL;
    }

    private function printSummary(): void
    {
        echo self::SEP . PHP_EOL;
        echo \sprintf('Summary: %d passed, %d skipped, %d failed', $this->passed, $this->skipped, $this->failed) . PHP_EOL;
        echo self::SEP . PHP_EOL;
    }

    /**
     * @phpstan-return never
     */
    private function skip(string $reason): void
    {
        throw new SkipScenario($reason);
    }

    /**
     * Volatile-field scrubber so Java/PHP outputs diff cleanly. Strips
     * server times, bearer JWT prefixes, UUIDs, PTNs, timestamp fields.
     */
    private function stripVolatileFields(string $line): string
    {
        $patterns = [
            '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})/' => '<TIMESTAMP>',
            '/\d{4}-\d{2}-\d{2}/' => '<DATE>',
            '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i' => '<UUID>',
            '/PTN-[A-Za-z0-9]+/' => '<PTN>',
            '/(bearer prefix: )[A-Za-z0-9._-]+/i' => '$1<JWT>',
        ];

        return \preg_replace(\array_keys($patterns), \array_values($patterns), $line) ?? $line;
    }

    private function redactBaseUrl(string $baseUrl): string
    {
        return \rtrim($baseUrl, '/');
    }

    private function redactKey(string $key): string
    {
        if (\strlen($key) <= 4) {
            return '****';
        }

        return \substr($key, 0, 4) . '...' . \substr($key, -2);
    }

    private function jwtPrefix(string $jwt): string
    {
        return \substr($jwt, 0, 12);
    }

    /**
     * @return array{0: \Psr\Http\Client\ClientInterface, 1: Psr17Factory}
     */
    private function buildHttpClient(): array
    {
        $factory = new Psr17Factory();
        if (!$this->offline) {
            // Real HTTP. We use Nyholm's PSR-7 factory + the partner's chosen
            // PSR-18 client. For dev convenience the smoke test uses a
            // MockClient-free composer-provided HTTP client if available.
            $clientClass = '\\GuzzleHttp\\Client';
            if (\class_exists($clientClass)) {
                /** @var \Psr\Http\Client\ClientInterface $client */
                $client = new $clientClass([
                    'timeout' => $this->cfg !== null ? 30 : 30,
                    'connect_timeout' => 10,
                ]);

                return [$client, $factory];
            }
            // Try Symfony HttpClient -> PSR-18 adapter.
            if (\class_exists('\\Symfony\\Component\\HttpClient\\Psr18Client')) {
                /** @var \Psr\Http\Client\ClientInterface $client */
                $client = new \Symfony\Component\HttpClient\Psr18Client();

                return [$client, $factory];
            }
            throw new \RuntimeException(
                'No PSR-18 HTTP client implementation found on the classpath. '
                . 'Install one with: composer require guzzlehttp/guzzle. '
                . 'Or rerun with --offline to replay test fixtures.',
            );
        }

        // --offline: replay fixtures in the same order the scenarios make calls.
        $mock = new MockHttpClient();
        $this->preloadOfflineFixtures($mock);

        return [$mock, $factory];
    }

    private function buildSmobilpayConfig(): SmobilpayConfig
    {
        return new SmobilpayConfig(
            baseUrl: $this->cfg->baseUrl,
            publicKey: $this->cfg->publicKey,
            secretKey: $this->cfg->secretKey,
            apiVersion: $this->cfg->apiVersion ?? SmobilpayConfig::DEFAULT_API_VERSION,
        );
    }

    /**
     * Queue every fixture response in the order the 15 scenarios will fetch
     * them. The first response is the OAuth token mint; then ping, then ping
     * after refresh, then account, etc.
     */
    private function preloadOfflineFixtures(MockHttpClient $mock): void
    {
        $fixturesDir = __DIR__ . '/../tests/Fixtures';
        $load = static function (string $name) use ($fixturesDir): Response {
            $path = $fixturesDir . '/' . $name;
            $body = \file_get_contents($path);
            \assert($body !== false, "missing fixture: {$name}");

            return new Response(200, ['Content-Type' => 'application/json'], $body);
        };

        // OAuth mint (covers all subsequent authenticated calls within TTL).
        $mock->addResponse($load('oauth-token.json'));
        // 1. Ping
        $mock->addResponse($load('ping.json'));
        // 2. Token refresh — re-mint then re-ping
        $mock->addResponse($load('oauth-token.json'));
        $mock->addResponse($load('ping.json'));
        // 3. Account
        $mock->addResponse($load('account.json'));
        // 4. Merchants
        $mock->addResponse($load('merchants.json'));
        // 5. Services
        $mock->addResponse($load('services.json'));
        // 6. Cashout discover + quote
        $mock->addResponse($load('cashout.json'));
        $mock->addResponse($load('quote-response.json'));
        // 7. Bill discover + quote
        $mock->addResponse($load('bill.json'));
        $mock->addResponse($load('quote-response.json'));
        // 8. Topup discover + quote
        $mock->addResponse($load('topup.json'));
        $mock->addResponse($load('quote-response.json'));
        // 9. Voucher discover + quote
        $mock->addResponse($load('voucher.json'));
        $mock->addResponse($load('quote-response.json'));
        // 10. Product discover + quote
        $mock->addResponse($load('product.json'));
        $mock->addResponse($load('quote-response.json'));
        // 11. Subscription discover + quote
        $mock->addResponse($load('subscription.json'));
        $mock->addResponse($load('quote-response.json'));
        // 12. Cashin discover + quote
        $mock->addResponse($load('cashin.json'));
        $mock->addResponse($load('quote-response.json'));
        // 13. verifyServiceNumber
        $mock->addResponse($load('verify-true.json'));
        // 14. validateAccount
        $mock->addResponse($load('customer-account.json'));
        // 15. History
        $mock->addResponse($load('history.json'));
    }

    private static function resolveConfigPath(?string $arg): string
    {
        if ($arg !== null && $arg !== '') {
            return $arg;
        }
        $env = \getenv('SMOBILPAY_SMOKE_CONFIG');
        if (\is_string($env) && $env !== '') {
            return $env;
        }

        return 'smoke-test.json';
    }

    private static function printHelp(): void
    {
        echo <<<HELP
smoke-test — exercise the smobilpay-php-client against a real (or fixture-backed) partner env.

USAGE
  smoke-test [PATH] [--offline] [--strip-volatile]

CONFIG RESOLUTION (in order):
  1. PATH positional argument
  2. SMOBILPAY_SMOKE_CONFIG environment variable
  3. ./smoke-test.json in the current working directory

FLAGS
  --offline         Replay fixture JSON instead of hitting the network. Useful as
                    a self-documenting demo and to smoke-test the harness itself.
  --strip-volatile  Scrub timestamps, JWT prefixes, UUIDs and PTNs from detail
                    lines so the output diffs cleanly against the Java client's
                    `./gradlew runSmokeTest`. Same regex on both sides.

EXIT CODES
  0   all scenarios passed or skipped
  1   one or more scenarios failed
  2   config error before client could start


HELP;
    }
}

final class SkipScenario extends \RuntimeException
{
}
