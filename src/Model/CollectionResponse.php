<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

use DateTimeImmutable;

/**
 * Response from `POST /v2/collectstd` confirming a payment collection.
 *
 * Note: when the request `x-api-version: 3.0.0` header is set, a
 * {@see PaymentStatusType::SUCCESS} status is rewritten to
 * {@see PaymentStatusType::PENDING} server-side. Poll `/v2/verifytx` or
 * wait for the `callbackUrl` webhook to learn the final status.
 *
 * `$ptn` is globally unique; `$receiptNumber` is bound to the agent context
 * and is not globally unique.
 */
final readonly class CollectionResponse
{
    public function __construct(
        public string $ptn,
        public DateTimeImmutable $timestamp,
        public ?float $agentBalance,
        public ?string $receiptNumber,
        public ?string $veriCode,
        public ?float $priceLocalCur,
        public ?float $priceSystemCur,
        public ?string $localCur,
        public ?string $systemCur,
        public ?string $trid,
        public ?string $pin,
        public PaymentStatusType $status,
        public string $payItemId,
        public ?string $payItemDescr,
        public ?string $tag = null,
    ) {
    }
}
