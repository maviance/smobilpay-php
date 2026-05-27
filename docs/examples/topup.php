<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Maviance\Smobilpay\Model\QuoteRequest;

[$client, $cfg] = example_client();
if ($cfg->topup === null) {
    fwrite(STDERR, "Add a 'topup' block to smoke-test.json to run this example.\n");
    exit(2);
}

$topups = $client->masterdata()->topups(serviceid: $cfg->topup['serviceId']);
if ($topups === []) { fwrite(STDERR, "No topups.\n"); exit(1); }
$item = $topups[0];
echo "picked: {$item->payItemId} ({$item->amountType->value})\n";

$quote = $client->initiate()->quote(new QuoteRequest($cfg->topup['amount'], $item->payItemId));
echo "quoteId:   {$quote->quoteId->toString()}\n";
echo "expiresAt: {$quote->expiresAt->format(DATE_ATOM)}\n";
echo "price:     {$quote->priceLocalCur} {$quote->localCur}\n";
