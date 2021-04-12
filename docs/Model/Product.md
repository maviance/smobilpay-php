# Product

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**serviceid** | **int** | Unique  service Identifier. Identifies the service this product belongs to. | 
**merchant** | **string** | Unique  merchant code of associated merchant | 
**payItemId** | **string** | Unique  Payment Item ID identifying the product to be purchased | 
**payItemDescr** | **string** | Contains optional description about payment details (e.g. merchant provided bill types) | [optional] 
**amountType** | **string** | &#x27;Supported amount type for the payment of this product:&#x27; &#x27;\&quot;FIXED\&quot; -&gt; Product needs to be paid in full by the amount provided in “amount”&#x27; &#x27;\&quot;CUSTOM\&quot; -&gt; Amount must be freely entered&#x27; | 
**localCur** | **string** | Local currency of service. (Format: ISO 4217) | 
**name** | **string** | Customer friendly name for product to used for presentation | 
**amountLocalCur** | **float** | Cost of product in local currency – only set for FIXED amounts. | [optional] 
**description** | **string** | Optional description of product | [optional] 
**optStrg** | **string** | Optional string field | [optional] 
**optNmb** | **float** | Optional number field | [optional] 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

