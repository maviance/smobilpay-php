<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Api;

use Maviance\Smobilpay\Http\HttpTransport;
use Maviance\Smobilpay\Http\QueryParams;
use Maviance\Smobilpay\Model\Cashin;
use Maviance\Smobilpay\Model\Cashout;
use Maviance\Smobilpay\Model\Merchant;
use Maviance\Smobilpay\Model\Product;
use Maviance\Smobilpay\Model\Service;
use Maviance\Smobilpay\Model\Topup;

/**
 * Static reference data: merchants, services, and the payment-item catalogs
 * needed to drive a payment UI. Backed by the partner spec `Masterdata` tag.
 */
final class MasterdataApi
{
    public function __construct(private readonly HttpTransport $transport)
    {
    }

    /**
     * `GET /v2/merchant` — every merchant supported by the system.
     *
     * @return list<Merchant>
     */
    public function merchants(): array
    {
        /** @var list<Merchant> $r */
        $r = $this->transport->get('/v2/merchant', QueryParams::of(), [Merchant::class]);

        return $r;
    }

    /**
     * `GET /v2/service` — every service supported by the system.
     *
     * @return list<Service>
     */
    public function services(): array
    {
        /** @var list<Service> $r */
        $r = $this->transport->get('/v2/service', QueryParams::of(), [Service::class]);

        return $r;
    }

    /**
     * `GET /v2/product` — purchasable products, optionally filtered by service id.
     *
     * @return list<Product>
     */
    public function products(?int $serviceid = null): array
    {
        /** @var list<Product> $r */
        $r = $this->transport->get(
            '/v2/product',
            QueryParams::of()->add('serviceid', $serviceid),
            [Product::class],
        );

        return $r;
    }

    /**
     * `GET /v2/voucher` — purchasable vouchers, optionally filtered by service id.
     * The digital code is delivered on `CollectionResponse::$pin` on a
     * successful collection.
     *
     * @return list<Product>
     */
    public function vouchers(?int $serviceid = null): array
    {
        /** @var list<Product> $r */
        $r = $this->transport->get(
            '/v2/voucher',
            QueryParams::of()->add('serviceid', $serviceid),
            [Product::class],
        );

        return $r;
    }

    /**
     * `GET /v2/topup` — top-up packages, optionally filtered by service id.
     *
     * @return list<Topup>
     */
    public function topups(?int $serviceid = null): array
    {
        /** @var list<Topup> $r */
        $r = $this->transport->get(
            '/v2/topup',
            QueryParams::of()->add('serviceid', $serviceid),
            [Topup::class],
        );

        return $r;
    }

    /**
     * `GET /v2/cashin` — cash-in (disbursement) packages, optionally filtered by service id.
     *
     * @return list<Cashin>
     */
    public function cashins(?int $serviceid = null): array
    {
        /** @var list<Cashin> $r */
        $r = $this->transport->get(
            '/v2/cashin',
            QueryParams::of()->add('serviceid', $serviceid),
            [Cashin::class],
        );

        return $r;
    }

    /**
     * `GET /v2/cashout` — cash-out (collection) packages, optionally filtered by service id.
     *
     * @return list<Cashout>
     */
    public function cashouts(?int $serviceid = null): array
    {
        /** @var list<Cashout> $r */
        $r = $this->transport->get(
            '/v2/cashout',
            QueryParams::of()->add('serviceid', $serviceid),
            [Cashout::class],
        );

        return $r;
    }
}
