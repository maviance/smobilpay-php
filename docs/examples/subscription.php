<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Maviance\Smobilpay\Model\QuoteRequest;

[$client, $cfg] = example_client();
if ($cfg->subscription === null) {
    fwrite(STDERR, "Add a 'subscription' block to smoke-test.json to run this example.\n");
    exit(2);
}

$subs = $client->initiate()->subscriptions(
    $cfg->subscription['merchant'],
    $cfg->subscription['serviceId'],
    $cfg->subscription['serviceNumber'] ?? null,
    $cfg->subscription['customerNumber'] ?? null,
);
if ($subs === []) { fwrite(STDERR, "No subscriptions.\n"); exit(1); }
$sub = $subs[0];
echo "picked: {$sub->payItemId} (customer={$sub->customerName}, amount={$sub->amountLocalCur} {$sub->localCur})\n";

$amount = $cfg->subscription['amount'] ?? (int) $sub->amountLocalCur;
$quote = $client->initiate()->quote(new QuoteRequest($amount, $sub->payItemId));
echo "quoteId:   {$quote->quoteId->toString()}\n";
echo "expiresAt: {$quote->expiresAt->format(DATE_ATOM)}\n";
