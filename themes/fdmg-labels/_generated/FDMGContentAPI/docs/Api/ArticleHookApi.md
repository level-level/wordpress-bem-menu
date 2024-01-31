# FDMGContentAPIClient\ArticleHookApi

All URIs are relative to https://dev-api.ota.fdmg.org/Distributor/level2.

| Method                                                         | HTTP request               | Description                             |
| -------------------------------------------------------------- | -------------------------- | --------------------------------------- |
| [**hooksHookNamePost()**](ArticleHookApi.md#hooksHookNamePost) | **POST** /hooks/{hookName} | Run hook to create or update an article |

## `hooksHookNamePost()`

```php
hooksHookNamePost($hook_name, $article): \FDMGContentAPIClient\Model\SuccessResponse
```

Run hook to create or update an article

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: apiKey
$config = FDMGContentAPIClient\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = FDMGContentAPIClient\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new FDMGContentAPIClient\Api\ArticleHookApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$hook_name = 'hook_name_example'; // string | Hook name to run. Should represent the site where the article is for.
$article = new \FDMGContentAPIClient\Model\Article(); // \FDMGContentAPIClient\Model\Article | Post data

try {
    $result = $apiInstance->hooksHookNamePost($hook_name, $article);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ArticleHookApi->hooksHookNamePost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name          | Type                                                           | Description                                                           | Notes |
| ------------- | -------------------------------------------------------------- | --------------------------------------------------------------------- | ----- |
| **hook_name** | **string**                                                     | Hook name to run. Should represent the site where the article is for. |
| **article**   | [**\FDMGContentAPIClient\Model\Article**](../Model/Article.md) | Post data                                                             |

### Return type

[**\FDMGContentAPIClient\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[apiKey](../../README.md#apiKey)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
