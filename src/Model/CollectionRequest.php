<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Ramsey\Uuid\UuidInterface;

/**
 * Request body for `POST /v2/collectstd`.
 *
 * Only `$quoteId`, `$customerPhonenumber`, and `$customerEmailaddress` are
 * required. The other fields are required only when the chosen
 * {@see Service} sets the corresponding `isReq*` flag.
 *
 * Two server-enforced field lengths are validated client-side for an early,
 * helpful error:
 *  - `$tag` ≤ 50 characters
 *  - `$callbackUrl` ≤ 255 characters
 */
final readonly class CollectionRequest
{
    public function __construct(
        public UuidInterface $quoteId,
        public string $customerPhonenumber,
        public string $customerEmailaddress,
        public ?string $customerName = null,
        public ?string $customerAddress = null,
        public ?string $customerNumber = null,
        public ?string $serviceNumber = null,
        public ?string $trid = null,
        public ?string $tag = null,
        public ?string $callbackUrl = null,
        public ?string $cdata = null,
    ) {
        if ($customerPhonenumber === '') {
            throw new SmobilpayConfigException('CollectionRequest customerPhonenumber must not be empty');
        }
        if ($customerEmailaddress === '') {
            throw new SmobilpayConfigException('CollectionRequest customerEmailaddress must not be empty');
        }
        if ($tag !== null && mb_strlen($tag) > 50) {
            throw new SmobilpayConfigException(\sprintf(
                'CollectionRequest tag exceeds 50-character limit: length=%d',
                mb_strlen($tag),
            ));
        }
        if ($callbackUrl !== null && mb_strlen($callbackUrl) > 255) {
            throw new SmobilpayConfigException(\sprintf(
                'CollectionRequest callbackUrl exceeds 255-character limit: length=%d',
                mb_strlen($callbackUrl),
            ));
        }
    }
}
