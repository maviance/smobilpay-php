# Merchant

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**merchant** | **string** | Unique  merchant code. Use this value whenever \&quot;merchant\&quot; is required in request parameters. | 
**name** | **string** | Name of merchant | 
**description** | **string** | Merchant description | 
**category** | **string** | deprecated  - will be removed in future versions. Use categories in services. | [optional] 
**country** | **string** | Country of service operation (ISO 3166-1 alpha-3) | 
**status** | **string** | Merchant Status (Active | Inactive) | 
**logo** | **string** | Points to a URL with the logo of the merchant if available. | [optional] 
**logoHash** | **string** | MD5 Hash of the logo provided via URL – if available. This field stores the hash of the logo. If the logo is updated the hash will change. | [optional] 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

