<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Exception;

use InvalidArgumentException;

/**
 * Thrown by SmobilpayConfig validators and by argument validators on the
 * Api\* classes when a caller-supplied value is invalid (missing required
 * field, exceeded length limit, mutually-exclusive arguments, etc.).
 *
 * Extends \InvalidArgumentException — distinct from the SmobilpayException
 * hierarchy so partners can catch programmer errors separately from server
 * or transport errors.
 */
final class SmobilpayConfigException extends InvalidArgumentException
{
}
