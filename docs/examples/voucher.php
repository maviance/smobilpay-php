<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Maviance\Smobilpay\Model\QuoteRequest;

[$client, $cfg] = example_client();
if ($cfg->voucher === null) {
    fwrite(STDERR, "Add a 'voucher' block to smoke-test.json to run this example.\n");
    exit(2);
}

$vouchers = $client->masterdata()->vouchers(serviceid: $cfg->voucher['serviceId']);
if ($vouchers === []) { fwrite(STDERR, "No vouchers.\n"); exit(1); }
$item = $vouchers[0];
echo "picked: {$item->payItemId} (amount={$item->amountLocalCur} {$item->localCur})\n";

$amount = $cfg->voucher['amount'] ?? (int) $item->amountLocalCur;
$quote = $client->initiate()->quote(new QuoteRequest($amount, $item->payItemId));
echo "quoteId:   {$quote->quoteId->toString()}\n";
echo "expiresAt: {$quote->expiresAt->format(DATE_ATOM)}\n";
echo "Voucher PIN would be returned on CollectionResponse->pin after /v2/collectstd.\n";
