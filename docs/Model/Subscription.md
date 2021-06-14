# Subscription

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**serviceNumber** | **string** | service number with merchant (e.g. policy number with an insurance company or tax number for a governmental institution) | 
**serviceid** | **string** | Unique Service Identifier | 
**merchant** | **string** | Unique merchant code | 
**payItemId** | **string** | Unique Payment Item ID identifying the subscription | 
**payItemDescr** | **string** | Optional description about payment details | [optional] 
**amountType** | **string** | &#x27;Supported amount type for the payment of this subscription:&#x27; &#x27;\&quot;FIXED\&quot; -&gt; subscription needs to be paid in full, (Payment amount &#x3D; subscription amount provided in \&quot;amount\&quot;)&#x27; &#x27;\&quot;PARTIAL\&quot; -&gt; Partial subscription amount can be paid. (Payment amount &lt; subscription amount provided in \&quot;amount\&quot;))&#x27; &#x27;\&quot;OVERPAY\&quot; -&gt; More than the actual subscription amount owed can be paid. (Payment amount &gt; subscription amount provided in \&quot;amount\&quot;). Overpayments are subject to country specific regulations and may be limited to a certain threshold. &#x27; &#x27;\&quot;CUSTOM\&quot; -&gt; Amount can be freely entered, independent of subscription amount provided in \&quot;amount\&quot;&#x27; | 
**name** | **string** | Title/Name of subscription | 
**localCur** | **string** | Local currency of service. (Format: ISO 4217) | 
**amountLocalCur** | **float** | Payable amount in local currency | 
**customerReference** | **string** | Optional Customer reference | [optional] 
**customerName** | **string** | Customer Name | [optional] 
**customerNumber** | **string** | Optional Customer number with merchant | [optional] 
**startDate** | [**\DateTime**](\DateTime.md) | Optional start date (Format: ISO 8601) | [optional] 
**dueDate** | [**\DateTime**](\DateTime.md) | Optional due due date (Format: ISO 8601) | [optional] 
**endDate** | [**\DateTime**](\DateTime.md) | Optional end date (Format: ISO 8601) | [optional] 
**optStrg** | **string** | Optional string field to carry additional information | [optional] 
**optNmb** | **float** | Optional number field to carry additional information | [optional] 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

