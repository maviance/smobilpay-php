<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Exception;

use Maviance\Smobilpay\Model\ApiError;
use Throwable;

/**
 * Thrown when the Smobilpay API returns a non-2xx response with (usually) a
 * parseable {@see ApiError} envelope.
 *
 * Match on {@see ApiError::$respCode} for programmatic handling — that is the
 * canonical machine identifier per the partner spec. Some endpoints (notably
 * 401 Unauthorized) return a bare status with no body; in that case
 * {@see error()} is null and {@see rawBody()} carries whatever the server sent.
 */
final class SmobilpayApiException extends SmobilpayException
{
    public function __construct(
        public readonly int $httpStatus,
        public readonly ?ApiError $error,
        public readonly ?string $rawBody,
        ?Throwable $previous = null,
    ) {
        parent::__construct(self::buildMessage($httpStatus, $error, $rawBody), $previous);
    }

    public function error(): ?ApiError
    {
        return $this->error;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    public function rawBody(): ?string
    {
        return $this->rawBody;
    }

    private static function buildMessage(int $httpStatus, ?ApiError $error, ?string $rawBody): string
    {
        if ($error !== null) {
            return \sprintf(
                'Smobilpay API error (HTTP %d, respCode=%d): %s',
                $httpStatus,
                $error->respCode,
                $error->devMsg ?? '<no devMsg>',
            );
        }
        $bodyHint = ($rawBody === null || $rawBody === '') ? '<empty body>' : $rawBody;

        return \sprintf('Smobilpay API error (HTTP %d): %s', $httpStatus, $bodyHint);
    }
}
