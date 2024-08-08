# Maviance\S3PApiClient\AccountValidationApi

All URIs are relative to */v2*

Method | HTTP request | Description
------------- | ------------- | -------------
[**validateGet**](AccountValidationApi.md#validateget) | **GET** /validate | Validate an account and retrieve customer name
[**verifyGet**](AccountValidationApi.md#verifyget) | **GET** /verify | Verify service number

# **validateGet**
> \Maviance\S3PApiClient\Model\CustomerAccount validateGet($xApiVersion, $destination, $serviceId)

Validate an account and retrieve customer name

Use this endpoint to validate an account and retrieve the associate customer name - if available

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\AccountValidationApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$destination = "destination_example"; // string | Destination
$serviceId = "serviceId_example"; // string | Unique Smobilpay service Identifier

try {
    $result = $apiInstance->validateGet($xApiVersion, $destination, $serviceId);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountValidationApi->validateGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **destination** | **string**| Destination |
 **serviceId** | **string**| Unique Smobilpay service Identifier |

### Return type

[**\Maviance\S3PApiClient\Model\CustomerAccount**](../Model/CustomerAccount.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **verifyGet**
> bool verifyGet($xApiVersion, $merchant, $serviceid, $serviceNumber)

Verify service number

For services that support verification (indicated by the \"isVerifiable\" flag) the service number can be provided to this endpoint. The system will verify wether or not the service number is valid with the selected service.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\AccountValidationApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$merchant = "merchant_example"; // string | Unique  merchant code
$serviceid = 56; // int | Unique  service Identifier
$serviceNumber = "serviceNumber_example"; // string | Service number with merchant (e.g. meter number in bills from a utility provider) for which to perform the bill payment

try {
    $result = $apiInstance->verifyGet($xApiVersion, $merchant, $serviceid, $serviceNumber);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountValidationApi->verifyGet: ', $e->getMessage(), PHP_EOL;
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

**bool**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

