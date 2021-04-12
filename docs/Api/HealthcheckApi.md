# Maviance\S3PApiClient\HealthcheckApi

All URIs are relative to */v2*

Method | HTTP request | Description
------------- | ------------- | -------------
[**pingGet**](HealthcheckApi.md#pingget) | **GET** /ping | Check on the availability of the api

# **pingGet**
> \Maviance\S3PApiClient\Model\Ping pingGet($xApiVersion)

Check on the availability of the api

This endpoint simply checks the existence and validity of the request on the server by returning a valid response object or an error message. Its primary purpose is to provide a feedback on whether or not the API is available. It also provides the current server time and timezone

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\HealthcheckApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info

try {
    $result = $apiInstance->pingGet($xApiVersion);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling HealthcheckApi->pingGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]

### Return type

[**\Maviance\S3PApiClient\Model\Ping**](../Model/Ping.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

