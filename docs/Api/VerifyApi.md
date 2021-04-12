# Maviance\S3PApiClient\VerifyApi

All URIs are relative to */v2*

Method | HTTP request | Description
------------- | ------------- | -------------
[**historystdGet**](VerifyApi.md#historystdget) | **GET** /historystd | Retrieve list of historic payment collection.
[**verifytxGet**](VerifyApi.md#verifytxget) | **GET** /verifytx | Get the current payment collection status

# **historystdGet**
> \Maviance\S3PApiClient\Model\PaymentStatus[] historystdGet($xApiVersion, $timestampFrom, $timestampTo)

Retrieve list of historic payment collection.

This endpoint allows the search for historic payment collection records by time that was provided during payment collection. Both parameters have to be provided!

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\VerifyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$timestampFrom = new \DateTime("2013-10-20"); // \DateTime | Start date of transactions in result set (ISO 8601)
$timestampTo = new \DateTime("2013-10-20"); // \DateTime | End date of transactions in result set (ISO 8601)

try {
    $result = $apiInstance->historystdGet($xApiVersion, $timestampFrom, $timestampTo);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VerifyApi->historystdGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **timestampFrom** | **\DateTime**| Start date of transactions in result set (ISO 8601) | [optional]
 **timestampTo** | **\DateTime**| End date of transactions in result set (ISO 8601) | [optional]

### Return type

[**\Maviance\S3PApiClient\Model\PaymentStatus[]**](../Model/PaymentStatus.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **verifytxGet**
> \Maviance\S3PApiClient\Model\PaymentStatus[] verifytxGet($xApiVersion, $ptn, $trid)

Get the current payment collection status

Call this endpoint to retrieve the current payment status by either transaction number (PTN) or the custom transaction reference (TRID) that was provided during payment collection. At least one of these parameters has to be provided!

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Maviance\S3PApiClient\Service\VerifyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$xApiVersion = "3.0.0"; // string | api version info
$ptn = "ptn_example"; // string | Unique payment collection transaction number
$trid = "trid_example"; // string | custom external transaction reference provided during payment collection

try {
    $result = $apiInstance->verifytxGet($xApiVersion, $ptn, $trid);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VerifyApi->verifytxGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **xApiVersion** | **string**| api version info | [default to 3.0.0]
 **ptn** | **string**| Unique payment collection transaction number | [optional]
 **trid** | **string**| custom external transaction reference provided during payment collection | [optional]

### Return type

[**\Maviance\S3PApiClient\Model\PaymentStatus[]**](../Model/PaymentStatus.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

