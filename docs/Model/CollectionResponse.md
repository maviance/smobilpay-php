# CollectionResponse

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**ptn** | **string** | Unique payment collection transaction number | 
**timestamp** | [**\DateTime**](\DateTime.md) | Timestamp of processing in  System (ISO 8601) | 
**agentBalance** | **float** | Current Balance of agent account AFTER collection in system currency | 
**receiptNumber** | **string** | Receipt number - alternative identifier of payment - bound to agent context and is NOT unique | 
**veriCode** | **string** | Verification code for receipt number | 
**priceLocalCur** | **float** | Price paid in local currency | 
**priceSystemCur** | **float** | Price paid in system currency | 
**localCur** | **string** | Local currency of service. (Format: ISO 4217) | 
**systemCur** | **string** | Currency of billing by  system (Format: ISO 4217) | 
**trid** | **string** | custom external transaction reference provided during payment collection | [optional] 
**pin** | **string** | Only for VOUCHER services - field returning a PIN or digital code. Will return “null” otherwise. | [optional] 
**status** | **string** | payment processing status | 
**payItemId** | **string** | Unique  Payment Item ID for payment item identification | [optional] 
**payItemDescr** | **string** | Contains optional description about payment details (e.g. merchant provided bill types) | [optional] 
**tag** | **null|string** | optional custom field to be freely used for internal payment collection referencing and tagging. Will be included in payment status responses and reports | [optional] 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

