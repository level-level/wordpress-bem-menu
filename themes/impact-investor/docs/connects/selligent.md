# Selligent

Selligent is a tool used by FD to send newsletters. This is used in the website by creating posts, linking those posts to a newsletter and sending that newsletter.

## Flow

The general flow for creating and sending a newsletter consists of three steps:
1. Registering posts to Selligent
2. Registering a newsletter to Selligent, with the already registerd posts
3. Sending the newsletter.

## Documentation

The functionality we need for Selligent can be summed up with a simplified documentation:

### Authentication

Authentication is done via an authorization header that is send with the request:

(See `ImpactInvestor\Connectors\Selligent\Client::get_auth_headers`)

```
## HMAC beveiliging
$timestamp = time();
$hash = hash_hmac('sha256', $timestamp . '-POST-' . '[[ENDPOINT]]', '[[APIKEY]]');

$options = [
  'headers' => [
    'Authorization: hmac ' . '[[APIUSER]]' . ':' . $hash . ':' . $timestamp,
    'Content-Type: application/json'
  ],
  'method' => 'POST',
  'data' => json_encode($data)
];
```

### Posting articles

Endpoint: https://messagent.fdmediagroep.nl/restapi/api/sync/lists/1980/articles

Data:
```
$data = [
  'publication' => 'IMPACT_INVESTOR',
  'articleId' => '[[ARTICLEID]]',
  'title' => '[[ARTICLETITLE]]',
  'teaser' => '[[ARTICLETEASER]]',
  'news_section' => '[[ARTICLECATEGORY]]',
  'img' => '[[ARTICLEIMGURL]]',
  'cta' => '[[ARTICLEURL]]'
];
```

### Create a newsletter

Endpoint: https://messagent.fdmediagroep.nl/restapi/api/sync/lists/1979/articles

Data:
```
$data = [
  'publication' => 'IMPACT_INVESTOR',
  'type' => 'WEEKLY',
  'subject' => '[[SUBJECT]]'
  'preheader' => '[[PREHEADER]]',
  'introduction' => '[[INTRO]]', 
  'article_1' => '[[ARTILCEID]]',
  'article_2' => '[[ARTILCEID]]',
  'article_3' => '[[ARTILCEID]]',
  'article_4' => '[[ARTILCEID]]',
  'article_5' => '[[ARTILCEID]]'
];
```

### Send a newsletter

Endpoint: https://messagent.fdmediagroep.nl/restapi/api/sync/campaigns/triggerbyjsonwithresult

Default newsletter type is `WEEKLY`. This will send the newsletter to all current recipients. Set the newsletter type to `MVP` for a test recipient list, that includes the `notifications@level-level.com` email.

```
$data = [
  'Gate' => 'NEWSLETTER_GENERATE_IMPACTINVESTOR',
  'Gateinput' => [
    'PUBLICATION' => 'IMPACT_INVESTOR',
    'TYPE' => 'WEEKLY'
  ]
];
```
