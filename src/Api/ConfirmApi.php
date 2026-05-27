<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Api;

use Maviance\Smobilpay\Http\HttpTransport;
use Maviance\Smobilpay\Model\CollectionRequest;
use Maviance\Smobilpay\Model\CollectionResponse;

/**
 * Execute payment collections against a previously-issued quote. Backed by
 * the partner spec `Confirm` tag.
 */
final class ConfirmApi
{
    public function __construct(private readonly HttpTransport $transport)
    {
    }

    /**
     * `POST /v2/collectstd` — execute a payment collection against a valid
     * (unexpired) quote.
     *
     * An HTTP 498 response indicates the quote has expired; re-quote before
     * retrying.
     */
    public function collect(CollectionRequest $request): CollectionResponse
    {
        /** @var CollectionResponse $r */
        $r = $this->transport->post('/v2/collectstd', $request, CollectionResponse::class);

        return $r;
    }
}
