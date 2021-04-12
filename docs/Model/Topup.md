# Topup

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**serviceid** | **int** | Unique  service Identifier. Idenfies the service this topup package belongs to. Use this value whenever “serviceid” is required in request parameters. | 
**merchant** | **string** | Unique  merchant code identifying the merchant this topup belongs to. | 
**payItemId** | **string** | Unique  Payment Item ID identifying the topup package to be purchased | 
**payItemDescr** | **string** | Contains optional description about payment details (e.g. merchant provided bill types) | [optional] 
**amountType** | **string** | &#x27;Supported amount type for the payment of this product:&#x27; &#x27;\&quot;FIXED\&quot; -&gt; Topup needs to be paid in full by the amount provided in \&quot;amount\&quot;&#x27; &#x27;\&quot;CUSTOM\&quot; -&gt; Amount must be freely entered&#x27; | 
**localCur** | **string** | Local currency of service. (Format: ISO 4217) | 
**name** | **string** | Customer friendly name for topup package to be displayed | 
**amountLocalCur** | **float** | Cost of topup package in local currency – only set for FIXED amounts.Otherwise null . | [optional] 
**description** | **string** | Optional description of topup package | [optional] 
**optStrg** | **string** | Optional string field | [optional] 
**optNmb** | **float** | Optional number field | [optional] 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

