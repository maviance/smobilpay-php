<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

use Maviance\Smobilpay\Exception\SmobilpayConfigException;

/**
 * Request body for `POST /v2/quotestd`.
 *
 *  - `$amount`    amount to be collected in the local currency of the
 *                 payment item (full integer, no decimals; subject to the
 *                 item's {@see AmountType}).
 *  - `$payItemId` payment item id from the masterdata catalog to quote.
 */
final readonly class QuoteRequest
{
    public function __construct(
        public int $amount,
        public string $payItemId,
    ) {
        if ($amount < 1) {
            throw new SmobilpayConfigException(\sprintf(
                'QuoteRequest amount must be >= 1, got %d',
                $amount,
            ));
        }
        if ($payItemId === '') {
            throw new SmobilpayConfigException('QuoteRequest payItemId must not be empty');
        }
    }
}
