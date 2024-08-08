# CollectionRequest

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**quote_id** | **string** | Quote Number of the related quote that was previously requested. | 
**customer_phonenumber** | **string** | Customer Phonenumber for regulatory compliance – international format with leading country code. E.g. “237699999999” for a fictional phone number 699999999 in Cameroon (237). | 
**customer_emailaddress** | **string** | Customer Email address for regulatory compliance. | 
**customer_name** | **string** | Customer Name for regulatory compliance - only mandatory if &lt;&lt;service.isReqCustomerName &#x3D; 1&gt;&gt; | [optional] 
**customer_address** | **string** | Customer Address for regulatory compliance - only mandatory if &lt;&lt;service.isReqCustomerAddress &#x3D; 1&gt;&gt; | [optional] 
**customer_number** | **string** | Customer number - only mandatory if &lt;&lt;service.isReqCustomerNumber &#x3D; 1&gt;&gt; | [optional] 
**service_number** | **string** | Service number – only mandatory if &lt;&lt;service.isReqServiceNumber &#x3D; 1&gt;&gt;. Usually contains the target of a payment collection. | [optional] 
**trid** | **string** | custom external transaction reference - custom field to be freely used for internal payment collection referencing. Should be unique. **NOTE:** The API does not manage transaction references (e.g. run unique validation) – this value needs to be managed by the client’s system. | [optional] 
**tag** | **string** | optional custom field to be freely used for internal payment collection referencing and tagging. Will be included in payment status responses and reports | [optional] 
**callback_url** | **string** | optional additional(!) custom endpoint that is to be notified vib webhook about the payment processing status changes | [optional] 
**cdata** | **string** | Custom valid json string containing extended - non standard - information needed for special purpose usecases | [optional] 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

