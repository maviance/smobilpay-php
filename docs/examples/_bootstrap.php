<?php
/**
 * Shared bootstrap for the runnable examples in this directory.
 *
 * Loads `smoke-test.json` from the repo root (or the path in
 * $SMOBILPAY_SMOKE_CONFIG) and constructs a SmobilpayClient. Each
 * example file under this directory then runs ONE flow (discover →
 * quote) and stops short of /v2/collectstd.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Maviance\Smobilpay\SmobilpayClient;
use Maviance\Smobilpay\SmobilpayConfig;
use Maviance\Smobilpay\Samples\SmokeTestConfig;

function example_client(): array
{
    $cfgPath = getenv('SMOBILPAY_SMOKE_CONFIG');
    if ($cfgPath === false || $cfgPath === '') {
        $cfgPath = dirname(__DIR__, 2) . '/smoke-test.json';
    }
    if (!is_file($cfgPath)) {
        fwrite(STDERR, "Config not found at {$cfgPath}.\n"
            . "Copy smoke-test.example.json to smoke-test.json and fill in your partner credentials.\n");
        exit(2);
    }

    $cfg = SmokeTestConfig::fromJsonFile($cfgPath);
    if (!class_exists(GuzzleClient::class)) {
        fwrite(STDERR, "Guzzle is required for the runnable examples. composer require guzzlehttp/guzzle\n");
        exit(2);
    }

    $config = new SmobilpayConfig(
        baseUrl:   $cfg->baseUrl,
        publicKey: $cfg->publicKey,
        secretKey: $cfg->secretKey,
        apiVersion: $cfg->apiVersion ?? SmobilpayConfig::DEFAULT_API_VERSION,
    );
    $http   = new GuzzleClient(['timeout' => 30, 'connect_timeout' => 10]);
    $factory = new HttpFactory();
    $client = SmobilpayClient::create($config, $http, $factory, $factory);

    return [$client, $cfg];
}
