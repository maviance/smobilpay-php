<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * Localized text entry. Used for service hints and field labels (e.g.
 * {@see Service::$labelServiceNumber}).
 */
final readonly class I18nText
{
    public function __construct(
        public string $language,
        public string $localText,
    ) {
    }
}
