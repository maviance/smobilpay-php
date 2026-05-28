<?php
declare(strict_types=1);
/**
 * Restricted account validation via /v2/validate. Requires KYC enablement.
 * Returns the customer name and tri-state status when available.
 */
require __DIR__ . '/_bootstrap.php';

use Maviance\Smobilpay\Exception\SmobilpayApiException;

[$client, $cfg] = example_client();
if ($cfg->validate === null) {
    fwrite(STDERR, "Add a 'validate' block to smoke-test.json to run this example.\n");
    exit(2);
}

try {
    $a = $client->accountValidation()->validateAccount(
        $cfg->validate['destination'],
        $cfg->validate['serviceId'],
    );
    echo "destination: {$a->destination}\n";
    echo "status:      {$a->status->value}\n";
    echo "name:        " . ($a->name ?? '<null>') . "\n";
} catch (SmobilpayApiException $e) {
    if ($e->httpStatus() === 401) {
        fwrite(STDERR, "/v2/validate is restricted and not enabled for this partner.\n"
            . "Contact your account manager to request enablement.\n");
        exit(2);
    }
    throw $e;
}
