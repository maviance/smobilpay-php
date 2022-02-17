# PaymentStatus

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**ptn** | **string** | Unique payment collection transaction number | 
**serviceid** | **string** | Unique  service Identifier | 
**merchant** | **string** | Merchant code | 
**timestamp** | [**\DateTime**](\DateTime.md) | Timestamp of processing in  System (ISO 8601) | 
**receiptNumber** | **string** | Receipt number - alternative identifier of payment - bound to agent context and is NOT unique | 
**veriCode** | **string** | Verification code for receipt number | 
**clearingDate** | [**\DateTime**](\DateTime.md) | Date on information of payment information has been sent to merchant – if supported (ISO 8601) | 
**trid** | **string** | custom external transaction reference provided during payment collection | 
**priceLocalCur** | **float** | Price paid in local currency | 
**priceSystemCur** | **float** | Price paid in system currency | 
**localCur** | **string** | Local currency of service. (e.g. XAF) (Format: ISO 4217) | 
**systemCur** | **string** | Currency of billing by  system (Format: ISO 4217) | 
**pin** | **string** | Digital Code to display to customer - if supplied by service | [optional] 
**status** | **string** | payment processing status | 
**payItemId** | **string** | Unique  Payment Item ID for payment item identification | [optional] 
**payItemDescr** | **string** | Contains optional description about payment details (e.g. merchant provided bill types) | [optional] 
**errorCode** | **float** | Error code | 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

