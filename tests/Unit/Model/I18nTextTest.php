<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Model;

use Maviance\Smobilpay\Http\JsonSerializer;
use Maviance\Smobilpay\Model\I18nText;
use Maviance\Smobilpay\Model\Service;
use PHPUnit\Framework\TestCase;

final class I18nTextTest extends TestCase
{
    public function testBothFieldsCanBeNull(): void
    {
        $serializer = new JsonSerializer();

        /** @var I18nText $text */
        $text = $serializer->decode('{"language":"en","localText":null}', I18nText::class);
        self::assertSame('en', $text->language);
        self::assertNull($text->localText);

        /** @var I18nText $text2 */
        $text2 = $serializer->decode('{"language":null,"localText":null}', I18nText::class);
        self::assertNull($text2->language);
        self::assertNull($text2->localText);
    }

    public function testServiceWithNullLocalTextEntryDecodes(): void
    {
        // Regression: the server is observed to send `null` for I18nText.localText
        // in production. Decoding must succeed (treat as nullable) rather than
        // throw SmobilpayParseException.
        $json = json_encode([
            'serviceid' => 1, 'merchant' => 'ENEO', 'title' => 't',
            'description' => null, 'category' => null,
            'country' => 'CMR', 'localCur' => 'XAF',
            'type' => 'CASHOUT', 'status' => 'Active',
            'labelServiceNumber' => [
                ['language' => 'en', 'localText' => 'Meter number'],
                ['language' => 'fr', 'localText' => null],
            ],
        ]);

        $serializer = new JsonSerializer();
        /** @var Service $svc */
        $svc = $serializer->decode((string) $json, Service::class);
        self::assertNotNull($svc->labelServiceNumber);
        self::assertCount(2, $svc->labelServiceNumber);
        self::assertSame('Meter number', $svc->labelServiceNumber[0]->localText);
        self::assertNull($svc->labelServiceNumber[1]->localText);
    }
}
