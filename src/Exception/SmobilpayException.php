<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Exception;

use RuntimeException;
use Throwable;

/**
 * Base exception for all Smobilpay client failures.
 *
 * Concrete subtypes carry additional context:
 *  - {@see SmobilpayApiException} — non-2xx HTTP response from the partner API.
 *  - {@see SmobilpayAuthException} — OAuth 2.0 token issuance failure.
 *  - {@see SmobilpayTransportException} — network / PSR-18 transport failure.
 *
 * Catch this type to handle any client error generically; catch a subtype
 * for programmatic dispatch.
 */
abstract class SmobilpayException extends RuntimeException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
