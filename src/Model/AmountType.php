<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * How the payment amount is determined for a {@see PaymentItem}.
 *
 *  - FIXED   — must be paid in full at `amountLocalCur`.
 *  - CUSTOM  — caller chooses the amount.
 *  - PARTIAL — amount may be less than `amountLocalCur`.
 *  - OVERPAY — amount may exceed `amountLocalCur` (subject to country regulation).
 */
enum AmountType: string
{
    case FIXED = 'FIXED';
    case CUSTOM = 'CUSTOM';
    case PARTIAL = 'PARTIAL';
    case OVERPAY = 'OVERPAY';
}
