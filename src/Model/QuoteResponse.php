<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

/**
 * Quote response from `POST /v2/quotestd`. The `$quoteId` must be passed
 * back on a {@see CollectionRequest} within `$expiresAt`.
 */
final readonly class QuoteResponse
{
    public function __construct(
        public UuidInterface $quoteId,
        public DateTimeImmutable $expiresAt,
        public string $payItemId,
        public ?float $amountLocalCur,
        public ?float $priceLocalCur,
        public ?float $priceSystemCur,
        public ?string $localCur,
        public ?string $systemCur,
        public ?string $promotion = null,
    ) {
    }
}
