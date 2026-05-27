<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use Throwable;

/**
 * Thrown when the underlying PSR-18 HTTP client fails to send a request or
 * receive a response — typically DNS failures, connection refused, TLS
 * handshake failure, or read timeouts.
 *
 * The original PSR-18 exception is preserved on getPrevious() and on
 * psr18Exception() for callers that want the typed reference.
 */
final class SmobilpayTransportException extends SmobilpayException
{
    public function __construct(
        string $message,
        private readonly ?ClientExceptionInterface $psr18Exception = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $previous ?? $psr18Exception);
    }

    public function psr18Exception(): ?ClientExceptionInterface
    {
        return $this->psr18Exception;
    }
}
