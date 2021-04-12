# Maviance\S3PApiClient\AccountApi

All URIs are relative to */v2*

Method | HTTP request | Description
------------- | ------------- | -------------
[**accountGet**](AccountApi.md#accountget) | **GET** /account | Retrieve account information and remaining account balance

# **accountGet**
> \Maviance\S3PApiClient\Model\Account accountGet($xApiVersion)

Retrieve account information and remaining account balance

This endpoint returns the user’s account information – most notably the current balance of the user. Calling this service before and after **each** collection in order to retrieve the current limits and/or balance is **highly discouraged**. The recommended approach is as follows:   1. Only a successful payment collection transaction will affect the account balance. The corresponding endpoint will also return the current account balance after the collection in its result payload.   2. For unsuccessful payment transactions, the account balance will not be affected. The error message returns a verbose message as to why the transaction failed. There is no need to recheck the account after each error.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info

try {
    $result = $apiInstance->accountGet($xApiVersion);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]

### Return type

[**\Maviance\S3PApiClient\Model\Account**](../Model/Account.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

