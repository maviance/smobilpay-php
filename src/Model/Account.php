<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * The authenticated agent's account profile. Returned by `GET /v2/account`.
 */
final readonly class Account
{
    public function __construct(
        public float $balance,
        public string $currency,
        public string $key,
        public string $agentId,
        public string $agentName,
        public ?string $agentAddress,
        public ?string $agentPhonenumber,
        public string $companyName,
        public ?string $companyAddress,
        public ?string $companyPhonenumber,
        public float $limitMax,
        public float $limitRemaining,
    ) {
    }
}
