# Cashin

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**serviceid** | **int** | Unique  service Identifier. Idenfies the service this cashin package belongs to. Use this value whenever “serviceid” is required in request parameters. | 
**merchant** | **string** | Unique  merchant code identifying the merchant this cashin belongs to. | 
**payItemId** | **string** | Unique  Payment Item ID identifying the cashin package to use for the mobile wallet deposit | 
**payItemDescr** | **string** | Contains optional description about payment details | [optional] 
**amountType** | **string** | &#x27;Supported amount type for the payment of this product:&#x27; &#x27;\&quot;FIXED\&quot; -&gt; Cashin amount is fixed to the amount listed in the field \&quot;amount\&quot;&#x27; &#x27;\&quot;CUSTOM\&quot; -&gt; A custom amount can be entered&#x27; | 
**localCur** | **string** | Local currency of service. (Format: ISO 4217) | 
**name** | **string** | Customer friendly name for cashin package to be displayed | 
**amountLocalCur** | **float** | Cost of cash-in operation in local currency – only set for FIXED amounts.Otherwise null . | [optional] 
**description** | **string** | Optional description of cashin package | [optional] 
**optStrg** | **string** | Optional string field | [optional] 
**optNmb** | **float** | Optional number field | [optional] 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

