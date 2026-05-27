<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * A purchasable product or voucher. Returned by `GET /v2/product` and
 * `GET /v2/voucher`. `$amountType` is restricted to {@see AmountType::FIXED}
 * or {@see AmountType::CUSTOM}.
 *
 * For voucher purchases, the digital code is provided on the
 * {@see CollectionResponse::$pin} field of the successful collection.
 */
final readonly class Product implements PaymentItem
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
