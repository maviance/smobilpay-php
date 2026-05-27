<?php
declare(strict_types=1);
/**
 * Pre-payment service-number verification via /v2/verify.
 * Only meaningful for services with isVerifiable=true.
 */
require __DIR__ . '/_bootstrap.php';

[$client, $cfg] = example_client();
if ($cfg->verify === null) {
    fwrite(STDERR, "Add a 'verify' block to smoke-test.json to run this example.\n");
    exit(2);
}

$ok = $client->accountValidation()->verifyServiceNumber(
    $cfg->verify['merchant'],
    $cfg->verify['serviceId'],
    $cfg->verify['serviceNumber'],
);
echo "{$cfg->verify['serviceNumber']} for {$cfg->verify['merchant']}/{$cfg->verify['serviceId']}: "
    . ($ok ? 'valid' : 'invalid') . "\n";
