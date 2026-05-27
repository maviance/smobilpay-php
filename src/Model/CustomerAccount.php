<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

use Maviance\Smobilpay\Model\CustomerAccount\Status;

/**
 * Result of `GET /v2/validate` — an account-lookup response that reports
 * whether the supplied `$destination` is recognized by the service and,
 * where available, the associated customer name.
 *
 * The `$status` field distinguishes how the account was recognized — see
 * {@see Status} for the three possible outcomes.
 */
final readonly class CustomerAccount
{
    public function __construct(
        public Status $status,
        public ?string $name,
        public string $destination,
    ) {
    }
}
