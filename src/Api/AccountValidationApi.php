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
     * Returns `true` if the service number is valid.
     */
    public function verifyServiceNumber(string $merchant, int $serviceid, string $serviceNumber): bool
    {
        if ($merchant === '') {
            throw new SmobilpayConfigException('verifyServiceNumber: merchant must not be empty');
        }
        if ($serviceNumber === '') {
            throw new SmobilpayConfigException('verifyServiceNumber: serviceNumber must not be empty');
        }

        /** @var \stdClass $response */
        $response = $this->transport->get(
            '/v2/verify',
            QueryParams::of()
                ->add('merchant', $merchant)
                ->add('serviceid', $serviceid)
                ->add('serviceNumber', $serviceNumber),
            \stdClass::class,
        );
        // Some deployments return `true`/`false` as the raw body; others
        // return `{"valid": true}`. We accept both via stdClass + cast.
        if (isset($response->valid) && \is_bool($response->valid)) {
            return $response->valid;
        }

        return false;
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
     * cleared Maviance's internal validation and compliance review (KYC /
     * data-protection obligations apply to the returned customer name).
     * Unauthorized callers receive HTTP 401 as a
     * {@see \Maviance\Smobilpay\Exception\SmobilpayApiException}. Contact
     * your integration manager to request enablement.
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
