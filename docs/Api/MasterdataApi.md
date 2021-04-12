# Maviance\S3PApiClient\MasterdataApi

All URIs are relative to */v2*

Method | HTTP request | Description
------------- | ------------- | -------------
[**cashinGet**](MasterdataApi.md#cashinget) | **GET** /cashin | Retrieve available cashin packages
[**cashoutGet**](MasterdataApi.md#cashoutget) | **GET** /cashout | Retrieves available cashout packages
[**merchantGet**](MasterdataApi.md#merchantget) | **GET** /merchant | Retrieve list of merchants supported by the system.
[**productGet**](MasterdataApi.md#productget) | **GET** /product | Retrieve list of available products
[**serviceGet**](MasterdataApi.md#serviceget) | **GET** /service | Retrieve list of services supported by the system.
[**serviceIdGet**](MasterdataApi.md#serviceidget) | **GET** /service/{id} | Retrieve single service
[**topupGet**](MasterdataApi.md#topupget) | **GET** /topup | Retrieve available topup packages
[**voucherGet**](MasterdataApi.md#voucherget) | **GET** /voucher | Retrieve list of available vouchers to purchase

# **cashinGet**
> \Maviance\S3PApiClient\Model\Cashin[] cashinGet($xApiVersion, $serviceid)

Retrieve available cashin packages

This service provides available cashin packages to be made to the system.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\MasterdataApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$serviceid = 56; // int | Filter cashin packages for only the selected service

try {
    $result = $apiInstance->cashinGet($xApiVersion, $serviceid);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MasterdataApi->cashinGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **serviceid** | **int**| Filter cashin packages for only the selected service | [optional]

### Return type

[**\Maviance\S3PApiClient\Model\Cashin[]**](../Model/Cashin.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **cashoutGet**
> \Maviance\S3PApiClient\Model\Cashout[] cashoutGet($xApiVersion, $serviceid)

Retrieves available cashout packages

This service provides available cashout packages to be made to the system.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\MasterdataApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$serviceid = 56; // int | Filter cashout packages for only the selected service

try {
    $result = $apiInstance->cashoutGet($xApiVersion, $serviceid);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MasterdataApi->cashoutGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **serviceid** | **int**| Filter cashout packages for only the selected service | [optional]

### Return type

[**\Maviance\S3PApiClient\Model\Cashout[]**](../Model/Cashout.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **merchantGet**
> \Maviance\S3PApiClient\Model\Merchant[] merchantGet($xApiVersion)

Retrieve list of merchants supported by the system.

Provides merchants supported by the system. Every service is assigned to a merchant.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\MasterdataApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info

try {
    $result = $apiInstance->merchantGet($xApiVersion);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MasterdataApi->merchantGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]

### Return type

[**\Maviance\S3PApiClient\Model\Merchant[]**](../Model/Merchant.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **productGet**
> \Maviance\S3PApiClient\Model\Product[] productGet($xApiVersion, $serviceid)

Retrieve list of available products

This service provides a list of all available products for all services.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\MasterdataApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$serviceid = 56; // int | Filter products to only the selected service

try {
    $result = $apiInstance->productGet($xApiVersion, $serviceid);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MasterdataApi->productGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **serviceid** | **int**| Filter products to only the selected service | [optional]

### Return type

[**\Maviance\S3PApiClient\Model\Product[]**](../Model/Product.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **serviceGet**
> \Maviance\S3PApiClient\Model\Service[] serviceGet($xApiVersion)

Retrieve list of services supported by the system.

This service endpoint provides information about the services supported by . Each service has its own set of required input parameters which need to be provided during the collection request - starting with the prefix “isReq”. It is recommended that the application UI is configured based on the response values provided here. The service response will also specify the type of the service and thus detail how the related payment items can be retrieved and collected.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\MasterdataApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info

try {
    $result = $apiInstance->serviceGet($xApiVersion);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MasterdataApi->serviceGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]

### Return type

[**\Maviance\S3PApiClient\Model\Service[]**](../Model/Service.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **serviceIdGet**
> \Maviance\S3PApiClient\Model\Service serviceIdGet($xApiVersion, $id)

Retrieve single service

This service endpoint provides information about the selected service. Each service has its own set of required input parameters which need to be provided during the collection request - starting with the prefix “isReq”. It is recommended that the application UI is configured based on the response values provided here. The service response will also specify the type of the service and thus detail how the related payment items can be retrieved and collected.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\MasterdataApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$id = 56; // int | Unique  service Identifier.

try {
    $result = $apiInstance->serviceIdGet($xApiVersion, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MasterdataApi->serviceIdGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **id** | **int**| Unique  service Identifier. |

### Return type

[**\Maviance\S3PApiClient\Model\Service**](../Model/Service.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **topupGet**
> \Maviance\S3PApiClient\Model\Topup[] topupGet($xApiVersion, $serviceid)

Retrieve available topup packages

This service provides a list of all available topup packages. DEPRECTATED: Some providers will return a digital code for manual redeeming. This code will be provided in the response object of a successful collection. This functionality has been moved into the /voucher endpoint and will be removed in the next version of this API

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\MasterdataApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$serviceid = 56; // int | Filter topups to only the selected service

try {
    $result = $apiInstance->topupGet($xApiVersion, $serviceid);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MasterdataApi->topupGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **serviceid** | **int**| Filter topups to only the selected service | [optional]

### Return type

[**\Maviance\S3PApiClient\Model\Topup[]**](../Model/Topup.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **voucherGet**
> \Maviance\S3PApiClient\Model\Product[] voucherGet($xApiVersion, $serviceid)

Retrieve list of available vouchers to purchase

This service provides a list of all available vouchers for all services. A purchase of a voucher will return a digital code for manual redeeming. This code will be provided in the response object of a successful collection.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\MasterdataApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$serviceid = 56; // int | Filter products to only the selected service

try {
    $result = $apiInstance->voucherGet($xApiVersion, $serviceid);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MasterdataApi->voucherGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **serviceid** | **int**| Filter products to only the selected service | [optional]

### Return type

[**\Maviance\S3PApiClient\Model\Product[]**](../Model/Product.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

