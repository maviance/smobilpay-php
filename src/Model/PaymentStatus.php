<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

use DateTimeImmutable;

/**
 * Current state of a previously-issued payment collection. Returned by
 * `GET /v2/historystd` and `GET /v2/verifytx`.
 *
 * Note: `$serviceid` is a string here even though {@see Service::$serviceid}
 * is an int — this matches the partner spec.
 *
 * `$errorCode` is nullable because the acceptance environment returns
 * `null` here in practice, even though the spec types it as a number.
 */
final readonly class PaymentStatus
{
    public function __construct(
        public string $ptn,
        public ?string $serviceid,
        public ?string $merchant,
        public DateTimeImmutable $timestamp,
        public ?string $receiptNumber,
        public ?string $veriCode,
        public ?DateTimeImmutable $clearingDate,
        public ?string $trid,
        public ?float $priceLocalCur,
        public ?float $priceSystemCur,
        public ?string $localCur,
        public ?string $systemCur,
        public ?string $pin,
        public PaymentStatusType $status,
        public ?string $payItemId,
        public ?string $payItemDescr,
        public ?int $errorCode = null,
        public ?string $tag = null,
        public ?Commission $commission = null,
    ) {
    }
}
