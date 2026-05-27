<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

use DateTimeImmutable;

/**
 * Authenticated probe response from `GET /v2/ping`.
 *
 *  - `$time`    server time and timezone (ISO 8601 offset datetime)
 *  - `$version` server-emitted protocol version (`"3.0.0"` when the request
 *               `x-api-version` header is `"3.0.0"`, otherwise `"2.2.0"`)
 *  - `$nonce`   nonce echoed from the request
 *  - `$key`     public token of the user that sent the request
 */
final readonly class Ping
{
    public function __construct(
        public DateTimeImmutable $time,
        public string $version,
        public ?string $nonce,
        public string $key,
    ) {
    }
}
