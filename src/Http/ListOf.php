<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Http;

use Attribute;

/**
 * Decorates an `array` (or `?array`) constructor parameter so the
 * {@see JsonSerializer} decoder knows what element type to hydrate.
 *
 * ```php
 * public function __construct(
 *     #[ListOf(I18nText::class)] public ?array $labelServiceNumber = null,
 * ) {}
 * ```
 *
 * Without the attribute the decoder leaves the JSON array as a plain
 * associative array.
 *
 * @phpstan-template T of object
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class ListOf
{
    /**
     * @param class-string $elementType
     */
    public function __construct(public readonly string $elementType)
    {
    }
}
