<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Api;

use DateTimeImmutable;
use DateTimeZone;
use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\Http\HttpTransport;
use Maviance\Smobilpay\Http\QueryParams;
use Maviance\Smobilpay\Model\Account;
use Maviance\Smobilpay\Model\PaymentStatus;
use Maviance\Smobilpay\Model\Ping;

/**
 * Status and account verification endpoints. Backed by the partner spec
 * `Verify` tag.
 */
final class VerifyApi
{
    public function __construct(private readonly HttpTransport $transport)
    {
    }

    /**
     * `GET /v2/ping` — authenticated round-trip probe. Returns the server
     * time, version, the request nonce, and the public token used to
     * authenticate the request.
     */
    public function ping(): Ping
    {
        /** @var Ping $r */
        $r = $this->transport->get('/v2/ping', QueryParams::of(), Ping::class);

        return $r;
    }

    /**
     * `GET /v2/account` — the authenticated agent's account profile.
     */
    public function account(): Account
    {
        /** @var Account $r */
        $r = $this->transport->get('/v2/account', QueryParams::of(), Account::class);

        return $r;
    }

    /**
     * `GET /v2/verifytx` — current status of a payment collection by
     * `ptn` and/or `trid`. At least one parameter must be provided.
     *
     * @return list<PaymentStatus>
     */
    public function verifyTransaction(?string $ptn = null, ?string $trid = null): array
    {
        if ($ptn === null && $trid === null) {
            throw new SmobilpayConfigException(
                'verifyTransaction: at least one of ptn or trid must be provided',
            );
        }

        /** @var list<PaymentStatus> $r */
        $r = $this->transport->get(
            '/v2/verifytx',
            QueryParams::of()->add('ptn', $ptn)->add('trid', $trid),
            [PaymentStatus::class],
        );

        return $r;
    }

    /**
     * `GET /v2/historystd` by PTN — search history by exact payment
     * transaction number.
     *
     * @return list<PaymentStatus>
     */
    public function historyByPtn(string $ptn): array
    {
        if ($ptn === '') {
            throw new SmobilpayConfigException('historyByPtn: ptn must not be empty');
        }

        /** @var list<PaymentStatus> $r */
        $r = $this->transport->get(
            '/v2/historystd',
            QueryParams::of()->add('ptn', $ptn),
            [PaymentStatus::class],
        );

        return $r;
    }

    /**
     * `GET /v2/historystd` by TRID — search history by custom transaction
     * reference.
     *
     * @return list<PaymentStatus>
     */
    public function historyByTrid(string $trid): array
    {
        if ($trid === '') {
            throw new SmobilpayConfigException('historyByTrid: trid must not be empty');
        }

        /** @var list<PaymentStatus> $r */
        $r = $this->transport->get(
            '/v2/historystd',
            QueryParams::of()->add('trid', $trid),
            [PaymentStatus::class],
        );

        return $r;
    }

    /**
     * `GET /v2/historystd` by date range — search history by an inclusive
     * date range. Both `$from` and `$to` are interpreted at UTC midnight
     * and end-of-day respectively.
     *
     * @return list<PaymentStatus>
     */
    public function historyByDateRange(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        if ($to < $from) {
            throw new SmobilpayConfigException(
                'historyByDateRange: to date is before from date',
            );
        }
        $utc = new DateTimeZone('UTC');
        $fromAtMidnight = $from->setTimezone($utc)->setTime(0, 0, 0);
        $toAtEndOfDay = $to->setTimezone($utc)->setTime(23, 59, 59);

        /** @var list<PaymentStatus> $r */
        $r = $this->transport->get(
            '/v2/historystd',
            QueryParams::of()
                ->add('timestamp_from', $fromAtMidnight->format(DATE_ATOM))
                ->add('timestamp_to', $toAtEndOfDay->format(DATE_ATOM)),
            [PaymentStatus::class],
        );

        return $r;
    }
}
