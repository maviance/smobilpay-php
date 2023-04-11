# Smobilpay S3P API Client for PHP

Php library for the Smobilpay S3P API


> **Note**
> Only use this branch with PHP 8.1 and above

> **Note**
> Compatbility with PHP releases < 8.1 are being maintained in the v1.0 branch

## Getting Started

### Composer

To install the library via [Composer](https://getcomposer.org/), add composer.json:

```json
{
  "require": {
    "maviance/smobilpay-php": "*"
  }
}
```

## Usage

The official API documentation can be found at : https://apidocs.smobilpay.com

Samplecode to call account details:

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// inject credentials
$token = "public access key";
$secret = "access secret";
$url = "https://XXXXX";

$xApiVersion = "3.0.0"; // string | api version info

// init
$config = new \Maviance\S3PApiClient\Configuration();
$config->setHost($url);
$client = new \Maviance\S3PApiClient\ApiClient($token, $secret, ['verify' => false]);

// trigger request
$apiInstance = new Maviance\S3PApiClient\Service\AccountApi($client, $config);

try {
    $result = $apiInstance->accountGet($xApiVersion);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

Please visit https://apidocs.smobilpay.com for usage documentation

[![Latest Stable Version](https://poser.pugx.org/maviance/smobilpay-php/v/stable.svg)](https://packagist.org/packages/maviance/smobilpay-php) [![Latest Unstable Version](https://poser.pugx.org/maviance/smobilpay-php/v/unstable.svg)](https://packagist.org/packages/maviance/smobilpay-php) [![Total Downloads](https://poser.pugx.org/maviance/smobilpay-php/downloads.svg)](https://packagist.org/packages/maviance/smobilpay-php) [![License](https://poser.pugx.org/maviance/smobilpay-php/license.svg)](https://packagist.org/packages/maviance/smobilpay-php)


## Documentation for API Endpoints

All URIs are relative to */v2*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*AccountApi* | [**accountGet**](docs/Api/AccountApi.md#accountget) | **GET** /account | Retrieve account information and remaining account balance
*AccountValidationApi* | [**verifyGet**](docs/Api/AccountValidationApi.md#verifyget) | **GET** /verify | Verify service number
*ConfirmApi* | [**collectstdPost**](docs/Api/ConfirmApi.md#collectstdpost) | **POST** /collectstd | Execute payment collection
*HealthcheckApi* | [**pingGet**](docs/Api/HealthcheckApi.md#pingget) | **GET** /ping | Check on the availability of the api
*InitiateApi* | [**billGet**](docs/Api/InitiateApi.md#billget) | **GET** /bill | Get bill payment handler
*InitiateApi* | [**quotestdPost**](docs/Api/InitiateApi.md#quotestdpost) | **POST** /quotestd | Request quote with price details about the payment
*InitiateApi* | [**subscriptionGet**](docs/Api/InitiateApi.md#subscriptionget) | **GET** /subscription | Get subscription payment handler
*MasterdataApi* | [**cashinGet**](docs/Api/MasterdataApi.md#cashinget) | **GET** /cashin | Retrieve available cashin packages
*MasterdataApi* | [**cashoutGet**](docs/Api/MasterdataApi.md#cashoutget) | **GET** /cashout | Retrieves available cashout packages
*MasterdataApi* | [**merchantGet**](docs/Api/MasterdataApi.md#merchantget) | **GET** /merchant | Retrieve list of merchants supported by the system.
*MasterdataApi* | [**productGet**](docs/Api/MasterdataApi.md#productget) | **GET** /product | Retrieve list of available products
*MasterdataApi* | [**serviceGet**](docs/Api/MasterdataApi.md#serviceget) | **GET** /service | Retrieve list of services supported by the system.
*MasterdataApi* | [**serviceIdGet**](docs/Api/MasterdataApi.md#serviceidget) | **GET** /service/{id} | Retrieve single service
*MasterdataApi* | [**topupGet**](docs/Api/MasterdataApi.md#topupget) | **GET** /topup | Retrieve available topup packages
*MasterdataApi* | [**voucherGet**](docs/Api/MasterdataApi.md#voucherget) | **GET** /voucher | Retrieve list of available vouchers to purchase
*VerifyApi* | [**historystdGet**](docs/Api/VerifyApi.md#historystdget) | **GET** /historystd | Retrieve list of historic payment collection.
*VerifyApi* | [**verifytxGet**](docs/Api/VerifyApi.md#verifytxget) | **GET** /verifytx | Get the current payment collection status

## Documentation For Models

 - [Account](docs/Model/Account.md)
 - [Bill](docs/Model/Bill.md)
 - [Cashin](docs/Model/Cashin.md)
 - [Cashout](docs/Model/Cashout.md)
 - [CollectionRequest](docs/Model/CollectionRequest.md)
 - [CollectionResponse](docs/Model/CollectionResponse.md)
 - [Error](docs/Model/Error.md)
 - [I18nText](docs/Model/I18nText.md)
 - [Merchant](docs/Model/Merchant.md)
 - [PaymentStatus](docs/Model/PaymentStatus.md)
 - [Ping](docs/Model/Ping.md)
 - [Product](docs/Model/Product.md)
 - [Quote](docs/Model/Quote.md)
 - [QuoteRequest](docs/Model/QuoteRequest.md)
 - [Service](docs/Model/Service.md)
 - [Subscription](docs/Model/Subscription.md)
 - [Topup](docs/Model/Topup.md)