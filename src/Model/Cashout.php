<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * A cash-out item — a **collection** from a customer's mobile wallet.
 * Money flows *out* of the customer's wallet into the partner's balance.
 * Returned by `GET /v2/cashout`. `$amountType` is restricted to
 * {@see AmountType::FIXED} or {@see AmountType::CUSTOM}.
 */
final readonly class Cashout implements PaymentItem
{
    public function __construct(
        public int $serviceid,
        public string $merchant,
        public string $payItemId,
        public ?string $payItemDescr,
        public AmountType $amountType,
        public string $localCur,
        public ?string $name,
        public ?float $amountLocalCur,
        public ?string $description,
        public ?string $optStrg,
        public ?float $optNmb,
    ) {
    }
}
