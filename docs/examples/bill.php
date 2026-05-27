<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Maviance\Smobilpay\Model\QuoteRequest;

[$client, $cfg] = example_client();
if ($cfg->bill === null) {
    fwrite(STDERR, "Add a 'bill' block to smoke-test.json to run this example.\n");
    exit(2);
}

$bills = $client->initiate()->bills($cfg->bill['merchant'], $cfg->bill['serviceId'], $cfg->bill['serviceNumber']);
if ($bills === []) { fwrite(STDERR, "No bills.\n"); exit(1); }
$bill = $bills[0];
echo "picked: {$bill->payItemId} (amount={$bill->amountLocalCur} {$bill->localCur})\n";

$quote = $client->initiate()->quote(new QuoteRequest((int) $bill->amountLocalCur, $bill->payItemId));
echo "quoteId:   {$quote->quoteId->toString()}\n";
echo "expiresAt: {$quote->expiresAt->format(DATE_ATOM)}\n";
echo "price:     {$quote->priceLocalCur} {$quote->localCur}\n";
