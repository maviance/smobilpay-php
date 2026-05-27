<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/** Availability status of a {@see Service}. */
enum ServiceStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
}
