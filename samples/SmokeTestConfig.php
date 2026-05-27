<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Samples;

use RuntimeException;
use Throwable;

/**
 * Configuration record for {@see SmokeTest}, loaded from a single JSON file
 * with the same schema as the Java client's smoke-test.json.
 *
 * All per-flow blocks are optional: a block that is absent or null causes
 * the corresponding scenario to be skipped. See `smoke-test.example.json`
 * at the repo root for a fully-populated template.
 *
 * Every collection-bearing block (cashout, bill, topup, voucher, product,
 * subscription, cashin) also accepts an opt-in collect tail that promotes
 * the scenario from quote-only to a real `POST /v2/collectstd`. The opt-in
 * keys are:
 *
 *  - `collect`:              must be literal `true` to enable
 *  - `customerPhonenumber`:  required; for collections = payer MSISDN,
 *                            for disbursements (cashin) = recipient MSISDN
 *  - `customerEmailaddress`: required
 *  - optional pass-through:  `customerName`, `customerAddress`,
 *                            `customerNumber`, `serviceNumber`, `tag`,
 *                            `callbackUrl`, `cdata`, `trid`
 *
 * Identical schema (and identical opt-in semantics) to the Node.js client
 * at `nodejs/samples/smoke-test.js`.
 *
 * @phpstan-type CollectOptIn array{collect?: bool, customerPhonenumber?: string|null, customerEmailaddress?: string|null, customerName?: string|null, customerAddress?: string|null, customerNumber?: string|null, serviceNumber?: string|null, tag?: string|null, callbackUrl?: string|null, cdata?: string|null, trid?: string|null}
 * @phpstan-type CashoutCfg array{serviceId: int, amount: int}&CollectOptIn
 * @phpstan-type BillCfg    array{merchant: string, serviceId: int, serviceNumber: string}&CollectOptIn
 * @phpstan-type TopupCfg   array{serviceId: int, amount: int}&CollectOptIn
 * @phpstan-type VoucherCfg array{serviceId: int, amount?: int|null}&CollectOptIn
 * @phpstan-type ProductCfg array{serviceId: int, amount?: int|null}&CollectOptIn
 * @phpstan-type SubscriptionCfg array{merchant: string, serviceId: int, serviceNumber?: string|null, customerNumber?: string|null, amount?: int|null}&CollectOptIn
 * @phpstan-type CashinCfg  array{serviceId: int, amount: int}&CollectOptIn
 * @phpstan-type VerifyCfg  array{merchant: string, serviceId: int, serviceNumber: string}
 * @phpstan-type ValidateCfg array{destination: string, serviceId: int}
 */
final readonly class SmokeTestConfig
{
    /**
     * @param CashoutCfg|null      $cashout
     * @param BillCfg|null         $bill
     * @param TopupCfg|null        $topup
     * @param VoucherCfg|null      $voucher
     * @param ProductCfg|null      $product
     * @param SubscriptionCfg|null $subscription
     * @param CashinCfg|null       $cashin
     * @param VerifyCfg|null       $verify
     * @param ValidateCfg|null     $validate
     */
    public function __construct(
        public string $baseUrl,
        public string $publicKey,
        public string $secretKey,
        public ?string $apiVersion = null,
        public ?array $cashout = null,
        public ?array $bill = null,
        public ?array $topup = null,
        public ?array $voucher = null,
        public ?array $product = null,
        public ?array $subscription = null,
        public ?array $cashin = null,
        public ?array $verify = null,
        public ?array $validate = null,
    ) {
    }

    /**
     * Load and validate a JSON config file.
     */
    public static function fromJsonFile(string $path): self
    {
        if (!is_file($path)) {
            throw new SmokeTestConfigException(
                'Config file not found at ' . realpath($path) ?: $path
                . '. Pass a path as the first argument, set SMOBILPAY_SMOKE_CONFIG,'
                . ' or create ./smoke-test.json (see smoke-test.example.json).',
            );
        }
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new SmokeTestConfigException("Could not read {$path}");
        }
        try {
            /** @var mixed $data */
            $data = json_decode($raw, true, 512, \JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new SmokeTestConfigException("Could not parse {$path}: " . $e->getMessage(), $e);
        }
        if (!\is_array($data)) {
            throw new SmokeTestConfigException("{$path} did not decode to an object");
        }
        foreach (['baseUrl', 'publicKey', 'secretKey'] as $req) {
            if (!isset($data[$req]) || !\is_string($data[$req]) || $data[$req] === '') {
                throw new SmokeTestConfigException("Missing required field '{$req}' in {$path}");
            }
        }

        /** @var array<string, mixed> $data */
        return new self(
            baseUrl: $data['baseUrl'],
            publicKey: $data['publicKey'],
            secretKey: $data['secretKey'],
            apiVersion: \is_string($data['apiVersion'] ?? null) ? $data['apiVersion'] : null,
            cashout: self::cfgBlock($data, 'cashout'),
            bill: self::cfgBlock($data, 'bill'),
            topup: self::cfgBlock($data, 'topup'),
            voucher: self::cfgBlock($data, 'voucher'),
            product: self::cfgBlock($data, 'product'),
            subscription: self::cfgBlock($data, 'subscription'),
            cashin: self::cfgBlock($data, 'cashin'),
            verify: self::cfgBlock($data, 'verify'),
            validate: self::cfgBlock($data, 'validate'),
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>|null
     */
    private static function cfgBlock(array $data, string $key): ?array
    {
        $v = $data[$key] ?? null;
        if (!\is_array($v)) {
            return null;
        }
        /** @var array<string, mixed> $v */
        return $v;
    }
}

final class SmokeTestConfigException extends RuntimeException
{
}
