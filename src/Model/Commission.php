<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * Commission earned on a transaction. Present on
 * {@see PaymentStatus::$commission} only when the commission feature is
 * enabled for the merchant/service.
 */
final readonly class Commission
{
    public function __construct(
        public ?float $earnings,
        public ?string $currency,
    ) {
    }
}
