<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Exception;

use Throwable;

/**
 * Thrown when OAuth 2.0 token issuance at POST /oauth/token fails.
 *
 * Common causes per the partner spec:
 *  - HTTP 400 "unsupported_grant_type" — grant_type was not client_credentials.
 *  - HTTP 401 "invalid_client" — credentials missing, malformed, or rejected.
 *  - HTTP 502/504 — token issuance unavailable / timed out.
 */
final class SmobilpayAuthException extends SmobilpayException
{
    public function __construct(
        public readonly int $httpStatus,
        public readonly ?string $oauthError,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * OAuth 2.0 standard error identifier (e.g. "invalid_client") parsed
     * from the response body, or null if the server returned no body or the
     * body was not parseable JSON.
     */
    public function oauthError(): ?string
    {
        return $this->oauthError;
    }
}
