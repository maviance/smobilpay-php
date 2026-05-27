<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * A top-up package. Returned by `GET /v2/topup`. `$amountType` is restricted
 * to {@see AmountType::FIXED} or {@see AmountType::CUSTOM}.
 */
final readonly class Topup implements PaymentItem
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
