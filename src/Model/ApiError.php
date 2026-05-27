<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * Standard API error envelope. The `$respCode` is the canonical machine
 * identifier — match on this rather than parsing `$devMsg`. The full error
 * catalog is provided during partner onboarding.
 */
final readonly class ApiError
{
    public function __construct(
        public int $respCode,
        public ?string $devMsg = null,
        public ?string $usrMsg = null,
        public ?string $link = null,
    ) {
    }
}
