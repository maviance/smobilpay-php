<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

use DateTimeImmutable;

/**
 * A bill payment item. Returned by `GET /v2/bill`.
 *
 * For services with {@see ServiceType::SEARCHABLE_BILL} the response
 * contains all open bills for the provided service number; for
 * {@see ServiceType::NON_SEARCHABLE_BILL} it always contains a single bill.
 */
final readonly class Bill implements PaymentItem
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
        public ?BillType $billType = null,
        public ?float $penaltyAmount = null,
        public int $payOrder = 0,
        public ?string $serviceNumber = null,
        public ?string $billNumber = null,
        public ?string $customerNumber = null,
        public ?string $billMonth = null,
        public ?string $billYear = null,
        public ?DateTimeImmutable $billDate = null,
        public ?DateTimeImmutable $billDueDate = null,
    ) {
    }
}
