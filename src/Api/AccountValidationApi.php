<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Api;

use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\Http\HttpTransport;
use Maviance\Smobilpay\Http\QueryParams;
use Maviance\Smobilpay\Model\CustomerAccount;

/**
 * Pre-payment account checks. Backed by the partner spec
 * `Account Validation` tag.
 */
final class AccountValidationApi
{
    public function __construct(private readonly HttpTransport $transport)
    {
    }

    /**
     * `GET /v2/verify` — verify that a service number is valid for the
     * selected service. Only meaningful for services that report
     * `isVerifiable: true`.
     *
     * Per the partner spec the response body is a bare JSON boolean
     * (`true` or `false`), not a wrapped object.
     */
    public function verifyServiceNumber(string $merchant, int $serviceid, string $serviceNumber): bool
    {
        if ($merchant === '') {
            throw new SmobilpayConfigException('verifyServiceNumber: merchant must not be empty');
        }
        if ($serviceNumber === '') {
            throw new SmobilpayConfigException('verifyServiceNumber: serviceNumber must not be empty');
        }

        $body = $this->transport->getRaw(
            '/v2/verify',
            QueryParams::of()
                ->add('merchant', $merchant)
                ->add('serviceid', $serviceid)
                ->add('serviceNumber', $serviceNumber),
        );

        return trim($body) === 'true';
    }

    /**
     * `GET /v2/validate` — validate an account by destination (typically an
     * MSISDN or contract number) and retrieve the associated customer name,
     * when available.
     *
     * Unlike {@see verifyServiceNumber()}, this call returns a rich
     * {@see CustomerAccount} envelope with a tri-state status
     * (UNKNOWN, VALIDATED, VERIFIED) — so callers can distinguish a
     * syntactically-correct account from one that has been cross-checked
     * against the provider.
     *
     * **Restricted endpoint.** Access is granted only to partners who have
     * cleared the API provider's compliance review (data-protection
     * obligations apply to the returned customer name). Unauthorized
     * callers receive HTTP 401 as a
     * {@see \Maviance\Smobilpay\Exception\SmobilpayApiException}. Contact
     * your account manager to request enablement.
     */
    public function validateAccount(string $destination, int $serviceId): CustomerAccount
    {
        if ($destination === '') {
            throw new SmobilpayConfigException('validateAccount: destination must not be empty');
        }

        /** @var CustomerAccount $r */
        $r = $this->transport->get(
            '/v2/validate',
            QueryParams::of()
                ->add('destination', $destination)
                ->add('serviceId', $serviceId),
            CustomerAccount::class,
        );

        return $r;
    }
}
