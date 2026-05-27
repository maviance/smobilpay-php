<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Maviance\Smobilpay\Model\QuoteRequest;

[$client, $cfg] = example_client();
if ($cfg->cashin === null) {
    fwrite(STDERR, "Add a 'cashin' block to smoke-test.json to run this example.\n");
    exit(2);
}

$cashins = $client->masterdata()->cashins(serviceid: $cfg->cashin['serviceId']);
if ($cashins === []) { fwrite(STDERR, "No cashin items.\n"); exit(1); }
$item = $cashins[0];
echo "picked: {$item->payItemId} ({$item->amountType->value})\n";

$quote = $client->initiate()->quote(new QuoteRequest($cfg->cashin['amount'], $item->payItemId));
echo "quoteId:   {$quote->quoteId->toString()}\n";
echo "expiresAt: {$quote->expiresAt->format(DATE_ATOM)}\n";
echo "price:     {$quote->priceLocalCur} {$quote->localCur}\n";
