# Maviance\S3PApiClient\InitiateApi

All URIs are relative to */v2*

Method | HTTP request | Description
------------- | ------------- | -------------
[**billGet**](InitiateApi.md#billget) | **GET** /bill | Get bill payment handler
[**quotestdPost**](InitiateApi.md#quotestdpost) | **POST** /quotestd | Request quote with price details about the payment
[**subscriptionGet**](InitiateApi.md#subscriptionget) | **GET** /subscription | Get subscription payment handler

# **billGet**
> \Maviance\S3PApiClient\Model\Bill[] billGet($xApiVersion, $merchant, $serviceid, $serviceNumber)

Get bill payment handler

A request to this endpoint returns bill payment handler records for a service by a service number and retrieves its details if available. Bill payments come in 2 flavors – which are determined by the related service’s type: 1.  **SEARCHABLE_BILL** – When calling the endpoint for searchable bills, the result set will contain a list of all open bills for the selected service number. Each bill has its own Payment Item Identifier. 2.  **NON_SEARCHABLE_BILL** – When calling the endpoint for non-searchable bills, the result set will always contain a single bill item with a Payment Item ID to perform the collection for the provided service number.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\InitiateApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$merchant = "merchant_example"; // string | Unique  merchant code
$serviceid = 56; // int | Unique  service Identifier
$serviceNumber = "serviceNumber_example"; // string | Service number with merchant (e.g. meter number in bills from a utility provider) for which to perform the bill payment

try {
    $result = $apiInstance->billGet($xApiVersion, $merchant, $serviceid, $serviceNumber);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InitiateApi->billGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **merchant** | **string**| Unique  merchant code |
 **serviceid** | **int**| Unique  service Identifier |
 **serviceNumber** | **string**| Service number with merchant (e.g. meter number in bills from a utility provider) for which to perform the bill payment |

### Return type

[**\Maviance\S3PApiClient\Model\Bill[]**](../Model/Bill.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **quotestdPost**
> \Maviance\S3PApiClient\Model\Quote quotestdPost($xApiVersion, $body)

Request quote with price details about the payment

Calling this web-service requests a quote from the system for the payment collection of the selected payment item and the specified payment amount in the system. The amount is to be chosen based on the services amountType, so can either be fixed or a custom entered value. The third parameter specifies the payment method that the customer has chosen in order to pay for the collection, as there may be additional charges depending on the selected method. A quote will only remain available for short time (a few minutes) and will expire. A quote will return the actual costs involved in collecting the payment. A quote always needs to be requested before making a collection.\"

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\InitiateApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$body = new \Maviance\S3PApiClient\Model\QuoteRequest(); // \Maviance\S3PApiClient\Model\QuoteRequest | Quote Request

try {
    $result = $apiInstance->quotestdPost($xApiVersion, $body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InitiateApi->quotestdPost: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **body** | [**\Maviance\S3PApiClient\Model\QuoteRequest**](../Model/QuoteRequest.md)| Quote Request | [optional]

### Return type

[**\Maviance\S3PApiClient\Model\Quote**](../Model/Quote.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **subscriptionGet**
> \Maviance\S3PApiClient\Model\Subscription[] subscriptionGet($xApiVersion, $merchant, $serviceid, $serviceNumber)

Get subscription payment handler

A request to this endpoint looks up a subscription record for a service by service number and retrieves its details if available. When calling the endpoint the result set will contain a list of all available subscriptions registered under the provided service number. Each subscription has its own Payment Item Identifier.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\InitiateApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$merchant = "merchant_example"; // string | Unique merchant code
$serviceid = "serviceid_example"; // string | Unique service Identifier
$serviceNumber = "serviceNumber_example"; // string | service number with merchant (e.g. policy number with an insurance company or tax number for a governmental institution)

try {
    $result = $apiInstance->subscriptionGet($xApiVersion, $merchant, $serviceid, $serviceNumber);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InitiateApi->subscriptionGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **merchant** | **string**| Unique merchant code |
 **serviceid** | **string**| Unique service Identifier |
 **serviceNumber** | **string**| service number with merchant (e.g. policy number with an insurance company or tax number for a governmental institution) |

### Return type

[**\Maviance\S3PApiClient\Model\Subscription[]**](../Model/Subscription.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

