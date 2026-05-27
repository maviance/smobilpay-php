<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Maviance\Smobilpay\Model\QuoteRequest;

[$client, $cfg] = example_client();
if ($cfg->product === null) {
    fwrite(STDERR, "Add a 'product' block to smoke-test.json to run this example.\n");
    exit(2);
}

$products = $client->masterdata()->products(serviceid: $cfg->product['serviceId']);
if ($products === []) { fwrite(STDERR, "No products.\n"); exit(1); }
$item = $products[0];
echo "picked: {$item->payItemId} (amount={$item->amountLocalCur} {$item->localCur})\n";

$amount = $cfg->product['amount'] ?? (int) $item->amountLocalCur;
$quote = $client->initiate()->quote(new QuoteRequest($amount, $item->payItemId));
echo "quoteId:   {$quote->quoteId->toString()}\n";
echo "expiresAt: {$quote->expiresAt->format(DATE_ATOM)}\n";
