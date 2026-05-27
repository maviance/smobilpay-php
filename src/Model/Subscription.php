<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

use DateTimeImmutable;

/**
 * A subscription payment item. Returned by `GET /v2/subscription`.
 *
 * Looked up by either `serviceNumber` or `customerNumber`; the result set
 * contains all subscriptions found under the search criteria, each with its
 * own `payItemId`.
 */
final readonly class Subscription implements PaymentItem
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
        public ?string $serviceNumber = null,
        public ?string $customerReference = null,
        public ?string $customerName = null,
        public ?string $customerNumber = null,
        public ?DateTimeImmutable $startDate = null,
        public ?DateTimeImmutable $dueDate = null,
        public ?DateTimeImmutable $endDate = null,
    ) {
    }
}
