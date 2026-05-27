<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Auth;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Cached OAuth 2.0 bearer token. Immutable; {@see OAuth2TokenManager}
 * replaces the instance on refresh.
 */
final readonly class OAuth2Token
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public DateTimeImmutable $expiresAt,
    ) {
    }

    /**
     * True if `$now + $skew` is at or past `$expiresAt`.
     */
    public function isExpired(DateTimeInterface $now, DateInterval $skew): bool
    {
        $threshold = DateTimeImmutable::createFromInterface($now)->add($skew);

        return $threshold >= $this->expiresAt;
    }
}
