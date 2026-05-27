<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/** Classification of a {@see Bill}. */
enum BillType: string
{
    case REGULAR = 'REGULAR';
    case OVERDUE = 'OVERDUE';
}
