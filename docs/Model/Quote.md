# Quote

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**quoteId** | **string** | Unique quote number (UUID) | 
**expiresAt** | [**\DateTime**](\DateTime.md) | Expiration timestamp. The quote will only stay active the expiration time. (Format: ISO 8601) | 
**payItemId** | **string** | Unique  Payment Item ID identifying the item to request the quote for | 
**amountLocalCur** | **float** | Service amount in local currency | 
**priceLocalCur** | **float** | Price of payment in local currency | 
**priceSystemCur** | **float** | Price of payment in system currency | 
**localCur** | **string** | Local currency of service. (Format: ISO 4217) | 
**systemCur** | **string** | Currency of billing by  system. (Format: ISO 4217) | 
**promotion** | **string** | Optional comma seperated list of current or upcoming promotions offered by the quoted service | [optional] 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

