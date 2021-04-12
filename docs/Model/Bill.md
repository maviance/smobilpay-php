# Bill

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**billType** | **string** | &#x27;Type classification of the bill&#x27; &#x27;\&quot;REGULAR\&quot; -&gt; Regular Bill&#x27; &#x27;\&quot;OVERDUE\&quot; -&gt; Late bill that is overdue&#x27; | 
**penaltyAmount** | **double** | Late payment penalty amount in local currency. | 
**payOrder** | **float** | Payment order. The bill with the lowest number has to be paid first, starting with 1. If no payment order is enforced, all bills have the order 0. | 
**payItemId** | **string** | Unique  Payment Item ID for payment item identification | 
**payItemDescr** | **string** | Contains optional description about payment details (e.g. merchant provided bill types) | [optional] 
**serviceNumber** | **string** | service number with merchant (e.g. meter number in bills from a utility provider or a phone number for a mobile operator) | 
**serviceid** | **int** | Unique  Service Identifier | 
**merchant** | **string** | Unique  merchant code | 
**amountType** | **string** | &#x27;Supported amount type for the payment of this bill:&#x27; &#x27;\&quot;FIXED\&quot; -&gt; Bill needs to be paid in full, (Payment amount &#x3D; bill amount provided in \&quot;amountLocalCur\&quot;)&#x27; &#x27;\&quot;PARTIAL\&quot; -&gt; Partial bill amount can be paid. (Payment amount &lt; bill amount provided in \&quot;amountLocalCur\&quot;))&#x27; &#x27;\&quot;OVERPAY\&quot; -&gt; More than the actual bill amount owed can be paid. (Payment amount &gt; bill amount provided in \&quot;amountLocalCur\&quot;). Overpayments are subject to country specific regulations and may be limited to a certain threshold. &#x27; &#x27;\&quot;CUSTOM\&quot; -&gt; Amount can be freely entered, independent of bill amount provided in \&quot;amountLocalCur\&quot;&#x27; | 
**localCur** | **string** | Local currency of service.(eg: XAF) (Format: ISO 4217) | 
**amountLocalCur** | **float** | Open bill amount in local currency – (only searchable bills). | [optional] 
**billNumber** | **string** | Unique bill number in selected merchant service | [optional] 
**customerNumber** | **string** | Customer number with merchant | [optional] 
**billMonth** | **string** | Month of bill generation. Format: MM e.g. \&quot;03\&quot; for March | [optional] 
**billYear** | **string** | Year of bill generation. Format: YYYY e.g. \&quot;2016\&quot; | [optional] 
**billDate** | [**\DateTime**](\DateTime.md) | Exact date of bill generation (Format: ISO 8601) | [optional] 
**billDueDate** | [**\DateTime**](\DateTime.md) | Bill due date (Format: ISO 8601) | [optional] 
**optStrg** | **string** | Optional string field | [optional] 
**optNmb** | **float** | Optional number field | [optional] 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

