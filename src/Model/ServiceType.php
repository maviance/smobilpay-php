<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * Service type — drives which masterdata endpoint produces the corresponding
 * payment items for this service.
 */
enum ServiceType: string
{
    case SEARCHABLE_BILL = 'SEARCHABLE_BILL';
    case NON_SEARCHABLE_BILL = 'NON_SEARCHABLE_BILL';
    case PRODUCT = 'PRODUCT';
    case TOPUP = 'TOPUP';
    case SUBSCRIPTION = 'SUBSCRIPTION';
    case CASHIN = 'CASHIN';
    case CASHOUT = 'CASHOUT';
    case VOUCHER = 'VOUCHER';
}
