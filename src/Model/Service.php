<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

use Maviance\Smobilpay\Http\ListOf;

/**
 * A service offered by a merchant.
 *
 * The `isReq*` flags drive which fields a {@see CollectionRequest} must
 * include for this service. The `$type` determines which masterdata endpoint
 * produces the matching payment items.
 */
final readonly class Service
{
    /**
     * @param list<I18nText>|null $labelCustomerNumber
     * @param list<I18nText>|null $labelServiceNumber
     * @param list<I18nText>|null $hint
     */
    public function __construct(
        public int $serviceid,
        public string $merchant,
        public string $title,
        public ?string $description,
        public ?string $category,
        public string $country,
        public string $localCur,
        public ServiceType $type,
        public ServiceStatus $status,
        public bool $isReqCustomerName = false,
        public bool $isReqCustomerAddress = false,
        public bool $isReqCustomerNumber = false,
        public bool $isReqServiceNumber = false,
        public bool $isVerifiable = false,
        #[ListOf(I18nText::class)]
        public ?array $labelCustomerNumber = null,
        #[ListOf(I18nText::class)]
        public ?array $labelServiceNumber = null,
        #[ListOf(I18nText::class)]
        public ?array $hint = null,
        public ?string $validationMask = null,
        public ?int $denomination = null,
    ) {
    }
}
