<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * A merchant supported by the system. Every {@see Service} is assigned to a
 * merchant.
 *
 * The `$category` field is deprecated upstream and may be null or empty —
 * use service-level categories instead.
 */
final readonly class Merchant
{
    public function __construct(
        public string $merchant,
        public string $name,
        public ?string $description,
        public string $country,
        public MerchantStatus $status,
        public ?string $logo = null,
        public ?string $logoHash = null,
        public ?string $category = null,
    ) {
    }
}
