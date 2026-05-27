<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * Marker interface for the payment-item DTOs returned by the masterdata
 * endpoints (/v2/product, /v2/voucher, /v2/topup, /v2/cashin, /v2/cashout)
 * and the lookup endpoints (/v2/bill, /v2/subscription).
 *
 * All implementations expose the standard payment-item fields as
 * `public readonly` properties — see {@see Cashout}, {@see Cashin},
 * {@see Topup}, {@see Product}, {@see Bill}, {@see Subscription}. Use a
 * union type when you need polymorphic access:
 *
 * ```php
 * function quote(Cashin|Cashout|Topup|Product|Bill|Subscription $item): void
 * {
 *     // $item->payItemId, $item->amountLocalCur, $item->amountType, ...
 * }
 * ```
 *
 * The `payItemId` field is the value passed to {@see QuoteRequest} to
 * request pricing for the item.
 */
interface PaymentItem
{
}
