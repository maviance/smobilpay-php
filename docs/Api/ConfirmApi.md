# Maviance\S3PApiClient\ConfirmApi

All URIs are relative to */v2*

Method | HTTP request | Description
------------- | ------------- | -------------
[**collectstdPost**](ConfirmApi.md#collectstdpost) | **POST** /collectstd | Execute payment collection

# **collectstdPost**
> \Maviance\S3PApiClient\Model\CollectionResponse collectstdPost($xApiVersion, $body)

Execute payment collection

This endpoint executes a payment collection. Any collection will reduce the agent balance by service amount plus the service fee. Each collection must include a reference to corresponding quote and payment authorization token. Whether or not fields are mandatory depends on the service configuration

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\ConfirmApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$body = new \Maviance\S3PApiClient\Model\CollectionRequest(); // \Maviance\S3PApiClient\Model\CollectionRequest | Collection Request

try {
    $result = $apiInstance->collectstdPost($xApiVersion, $body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConfirmApi->collectstdPost: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **body** | [**\Maviance\S3PApiClient\Model\CollectionRequest**](../Model/CollectionRequest.md)| Collection Request | [optional]

### Return type

[**\Maviance\S3PApiClient\Model\CollectionResponse**](../Model/CollectionResponse.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

