<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Api;

use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\Http\HttpTransport;
use Maviance\Smobilpay\Http\QueryParams;
use Maviance\Smobilpay\Model\Bill;
use Maviance\Smobilpay\Model\QuoteRequest;
use Maviance\Smobilpay\Model\QuoteResponse;
use Maviance\Smobilpay\Model\Subscription;

/**
 * Lookups and quotes that prepare a payment collection. Backed by the
 * partner spec `Initiate` tag.
 */
final class InitiateApi
{
    public function __construct(private readonly HttpTransport $transport)
    {
    }

    /**
     * `GET /v2/bill` — search bills for a service number. For
     * `SEARCHABLE_BILL` services this returns every open bill; for
     * `NON_SEARCHABLE_BILL` services it returns a single item.
     *
     * @return list<Bill>
     */
    public function bills(string $merchant, int $serviceid, string $serviceNumber): array
    {
        if ($merchant === '') {
            throw new SmobilpayConfigException('bills: merchant must not be empty');
        }
        if ($serviceNumber === '') {
            throw new SmobilpayConfigException('bills: serviceNumber must not be empty');
        }

        /** @var list<Bill> $r */
        $r = $this->transport->get(
            '/v2/bill',
            QueryParams::of()
                ->add('merchant', $merchant)
                ->add('serviceid', $serviceid)
                ->add('serviceNumber', $serviceNumber),
            [Bill::class],
        );

        return $r;
    }

    /**
     * `GET /v2/subscription` — search subscriptions by service number
     * and/or customer number. Exactly one of them must be supplied (the
     * server rejects calls with neither).
     *
     * @return list<Subscription>
     */
    public function subscriptions(
        string $merchant,
        int $serviceid,
        ?string $serviceNumber = null,
        ?string $customerNumber = null,
    ): array {
        if ($merchant === '') {
            throw new SmobilpayConfigException('subscriptions: merchant must not be empty');
        }
        if ($serviceNumber === null && $customerNumber === null) {
            throw new SmobilpayConfigException(
                'subscriptions: either serviceNumber or customerNumber must be provided',
            );
        }

        /** @var list<Subscription> $r */
        $r = $this->transport->get(
            '/v2/subscription',
            QueryParams::of()
                ->add('merchant', $merchant)
                ->add('serviceid', $serviceid)
                ->add('serviceNumber', $serviceNumber)
                ->add('customerNumber', $customerNumber),
            [Subscription::class],
        );

        return $r;
    }

    /**
     * `POST /v2/quotestd` — request a price quote for a payment collection.
     * Quotes expire after a few minutes and must be requested fresh before
     * each collection.
     */
    public function quote(QuoteRequest $request): QuoteResponse
    {
        /** @var QuoteResponse $r */
        $r = $this->transport->post('/v2/quotestd', $request, QuoteResponse::class);

        return $r;
    }
}
