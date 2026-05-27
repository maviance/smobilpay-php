<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * Payment processing status.
 *
 * Note: when the request header `x-api-version: 3.0.0` is set, the server
 * rewrites `SUCCESS` to `PENDING` on {@see CollectionResponse::$status}.
 * Subsequent calls to `/v2/historystd` or `/v2/verifytx` return the final
 * status once the payment clears.
 */
enum PaymentStatusType: string
{
    case REVERSED = 'REVERSED';
    case PENDING = 'PENDING';
    case ERRORED = 'ERRORED';
    case SUCCESS = 'SUCCESS';
}
