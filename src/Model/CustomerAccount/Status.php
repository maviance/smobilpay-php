<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model\CustomerAccount;

/**
 * Account-recognition outcome reported by `/v2/validate`.
 *
 *  - UNKNOWN   — authenticity could be neither verified nor validated.
 *  - VALIDATED — account syntax has been internally confirmed (e.g. regex match).
 *  - VERIFIED  — account has been positively cross-checked against the provider.
 */
enum Status: string
{
    case UNKNOWN = 'UNKNOWN';
    case VALIDATED = 'VALIDATED';
    case VERIFIED = 'VERIFIED';
}
