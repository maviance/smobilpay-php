# Service

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**serviceid** | **int** | Unique  service Identifier. Use this value whenever “serviceid” is required in request parameters | 
**merchant** | **string** | Unique  merchant code | 
**title** | **string** | Public name of service | 
**description** | **string** | Service description | 
**category** | **string** | Category of service | 
**country** | **string** | Country of operation (ISO 3166-1 alpha-3) | 
**localCur** | **string** | Local currency of service. (Format: ISO 4217) | 
**type** | **string** | Type of service. This API will only provide services of the type | 
**status** | **string** | Service availability status | 
**isReqCustomerName** | **bool** | If set to true (1), the customers full name needs to be provided in the payment collection request. | 
**isReqCustomerAddress** | **bool** | If set to true (1), the customers address needs to be provided in the payment collection request. | 
**isReqCustomerNumber** | **bool** | If set to true (1), a customer number needs to be provided in the payment collection request. Customer number meaning is different for each service. | 
**isReqServiceNumber** | **bool** | If set to true (1), a service number needs to be provided in the payment collection request. Service number meaning is different for each service. | 
**labelCustomerNumber** | [**\Maviance\S3PApiClient\Model\I18nText[]**](I18nText.md) | Label for customer number in multiple languages (if available) for this service. | [optional] 
**labelServiceNumber** | [**\Maviance\S3PApiClient\Model\I18nText[]**](I18nText.md) | Label for service number in multiple languages (if available) for this service. | [optional] 
**isVerifiable** | **bool** | If set to true (1), then the service number provided for this service can be verified before making a payment request | 
**validationMask** | **string** | Optional mask for the service number entered during a payment for client side validations. All service numbers must comply to the mask in order to pass. The mask is a PCRE regular expression | [optional] 
**hint** | [**\Maviance\S3PApiClient\Model\I18nText[]**](I18nText.md) | Translation texts for the hint notes to be displayed to the customer for this service. | [optional] 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

