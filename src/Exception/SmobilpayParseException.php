<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Exception;

use Throwable;

/**
 * Thrown when a server-sent payload cannot be parsed into a typed value —
 * malformed JSON, unrecognized enum value, unparseable date, missing
 * required property, etc.
 *
 * Distinct from {@see SmobilpayApiException} (server returned non-2xx) and
 * {@see SmobilpayTransportException} (network failure). A
 * {@see SmobilpayParseException} indicates the server returned data the
 * client cannot make sense of — usually a contract drift that should be
 * reported to your API provider.
 */
final class SmobilpayParseException extends SmobilpayException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
