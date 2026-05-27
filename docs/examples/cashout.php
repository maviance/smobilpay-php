<?php
declare(strict_types=1);
/**
 * Discover a cashout payment item, request a quote, print details.
 * Does NOT call /v2/collectstd.
 */
require __DIR__ . '/_bootstrap.php';

use Maviance\Smobilpay\Model\QuoteRequest;

[$client, $cfg] = example_client();
if ($cfg->cashout === null) {
    fwrite(STDERR, "Add a 'cashout' block to smoke-test.json to run this example.\n");
    exit(2);
}

$cashouts = $client->masterdata()->cashouts(serviceid: $cfg->cashout['serviceId']);
if ($cashouts === []) { fwrite(STDERR, "No cashout items.\n"); exit(1); }
$item = $cashouts[0];
echo "picked: {$item->payItemId} ({$item->name}, {$item->amountType->value})\n";

$quote = $client->initiate()->quote(new QuoteRequest($cfg->cashout['amount'], $item->payItemId));
echo "quoteId:   {$quote->quoteId->toString()}\n";
echo "expiresAt: {$quote->expiresAt->format(DATE_ATOM)}\n";
echo "price:     {$quote->priceLocalCur} {$quote->localCur}\n";
