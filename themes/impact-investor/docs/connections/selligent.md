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
## HMAC authentication
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

#### Example response
```
{
	"publication": "IMPACT_INVESTOR",
	"articleId": 6,
	"title": "&#8216;Keep it simple and familiar to convince institutional investors on blended finance&#8217;",
	"teaser": "<p>Although blended finance is on everyone’s lips, deal flow and transaction sizes dropped sharply in 2020, a new Convergence report finds. The industry tracker makes recommendations on how to achieve scale. “We’re into the last decade of the 2030 agenda and we are nowhere near the trillions we will need to achieve it,” said Joan [&hellip;]</p>\n",
	"news_section": "",
	"img": "http://impact-investor.local/wp-content/uploads/2021/11/anp-375469043-1280x.jpg",
	"cta": "http://impact-investor.local/keep-it-simple-and-familiar-to-convince-institutional-investors-on-blended-finance/",
	"CREATED_DT": "2021-11-04T16:26:13.8569101+01:00",
	"Id": 14680
}
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

#### Example response
```
{
	"publication": "IMPACT_INVESTOR",
	"type": "MVP",
	"subject": "Newsletter test",
	"preheader": "Pre header",
	"introduction": "Introduction",
	"article_1": "6",
	"article_2": "9",
	"CREATED_DT": "2021-11-04T11:34:04.6474839+01:00",
	"Id": 1019
}
```

### Send a newsletter

Endpoint: https://messagent.fdmediagroep.nl/restapi/api/sync/campaigns/triggerbyjsonwithresult

Default newsletter type is `MVP`. This will send the newsletter to all current recipients. If a test type is needed, contact Impact Investor.

```
$data = [
  'Gate' => 'NEWSLETTER_GENERATE_IMPACTINVESTOR',
  'Gateinput' => [
    'PUBLICATION' => 'IMPACT_INVESTOR',
    'TYPE' => 'WEEKLY'
  ]
];
```

#### Example response:

**Success:**
```
{
	"IsSuccess": true,
	"Error": "",
	"Result": "[ok newsletter generated]"
}
```