<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Model;

/**
 * Localized text entry. Used for service hints and field labels (e.g.
 * {@see Service::$labelServiceNumber}).
 *
 * Both fields are typed as nullable. The partner spec marks them
 * `required` (meaning "the key is always present"), but the acceptance
 * environment is known to send `null` values for either field — we accept
 * that without throwing so partners can still consume the rest of the
 * Service masterdata.
 */
final readonly class I18nText
{
    public function __construct(
        public ?string $language,
        public ?string $localText,
    ) {
    }
}
