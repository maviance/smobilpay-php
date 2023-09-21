# CustomerAccount

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**status** | **string** | Status of customer account. Indicates if account was:  |Denomination|Amount|Valid| |-----|-----|-------| |UNKNOWN|authenticity of account could be neither verified nor validated| |VALIDATED|account syntax has been internally confirmed - e.g. against a regex check| |VERIFIED|account has been positivly crosschecked against service provider| | 
**name** | **string** | Customer name - if found | [optional] 
**destination** | **string** | Account destination identifier used during account lookup | 

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)


