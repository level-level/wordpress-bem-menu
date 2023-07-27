# Selligent

Selligent is a tool used by FD to send newsletters. This is used in the website by creating posts, linking those posts to a newsletter and sending that newsletter.

## Flow

The general flow for creating and sending a newsletter consists of three steps:

1. Registering posts to Selligent
2. Registering a newsletter to Selligent, with the already registerd posts
3. Sending the newsletter.

## Environments

There is a testing and a production environment available for connecting to the Selligent API. The login credentials for each environment can be found in the password manager.

- API Base Url test: https://selligent.devfd.nl
- API Base Url prod: https://messagent.fdmediagroep.nl

API base path: /restapi/api/sync/

The following credentials are required in the wp-config.

```php
define( 'II_SELLIGENT_TYPE', 'xxx' );
define( 'II_SELLIGENT_LIST_ID_NEWSLETTER', 'xxx' );
define( 'II_SELLIGENT_LIST_ID_ARTICLE', 'xxx' );
```

The rest of the settings can be configured in Theme options -> Selligent

## Authentication

### IP Whitelisting

Requests can only be sent from authorized ip addresses.
For local testing, the Pritnl VPN connection has been whitelisted.
For test, acceptance and production environments, the server IP's should be whitelisted as well.

### Authorization request

Authentication is done via an authorization header that is send with the request:

(See `FDMGLabels\Connectors\Selligent\Client::get_auth_headers`)

```php
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

## Posting articles

Endpoint: /lists/[[LIST_ID_NEWSLETTER]]/articles
Data:

```php
$data = [
	'publication' => 'IMPACT_INVESTOR',
	'articleId' => '[[ARTICLEID]]',
	'title' => '[[ARTICLETITLE]]',
	'teaser' => '[[ARTICLETEASER]]',
	'news_section' => '[[ARTICLECATEGORY]]',
	'img' => '[[ARTICLEIMGURL]]',
	'img_square' => '[[ARTICLEIMGSQUAREURL]]',
	'cta' => '[[ARTICLEURL]]'
	'reading_time' => '[[READINGTIMEINMINUTES]]'
	'publication_time' => '[[FORMATTEDPUBLICATIONTIME]]'
];
```

### Example response:

**Success:**

```json
{
	"publication": "IMPACT_INVESTOR",
	"articleId": 6,
	"title": "&#8216;Keep it simple and familiar to convince institutional investors on blended finance&#8217;",
	"teaser": "<p>Although blended finance is on everyone’s lips, deal flow and transaction sizes dropped sharply in 2020, a new Convergence report finds. The industry tracker makes recommendations on how to achieve scale. “We’re into the last decade of the 2030 agenda and we are nowhere near the trillions we will need to achieve it,” said Joan [&hellip;]</p>\n",
	"news_section": "",
	"img": "http://fdmg-labels.local/wp-content/uploads/2021/11/anp-375469043-1280x.jpg",
	"img_square": "http://fdmg-labels.local/wp-content/uploads/2021/11/anp-375469043-1280x.jpg",
	"cta": "http://fdmg-labels.local/keep-it-simple-and-familiar-to-convince-institutional-investors-on-blended-finance/",
	"reading_time": 3,
	"publication_time": "08:12",
	"CREATED_DT": "2021-11-04T16:26:13.8569101+01:00",
	"Id": 14680
}
```

## Create a newsletter

Endpoint: /lists/[[LIST_ID_ARTICLE]]/articles

Data:

```php
$data = [
	'publication' => 'IMPACT_INVESTOR',
	'type' => 'WEEKLY',
	'subject' => '[[SUBJECT]]'
	'preheader' => '[[PREHEADER]]',
	'introduction' => '[[INTRO]]',
	'articlegroup_1' => '[[ARTILCEID,ARTILCEID]]',
	'articlegroup_2' => '[[ARTILCEID,ARTILCEID]]',
	'articlegroup_3' => '[[ARTILCEID,ARTILCEID]]',
	'articlegroup_4' => '[[ARTILCEID,ARTILCEID]]',
	'test' => '[[TESTMODE]]'
];
```

### Example response:

**Success:**

```json
{
	"publication": "IMPACT_INVESTOR",
	"type": "MVP",
	"subject": "Newsletter test",
	"preheader": "Pre header",
	"introduction": "Introduction",
	"articlegroup_1": "6,7",
	"articlegroup_2": "9",
	"articlegroup_3": "9,10,11",
	"articlegroup_4": "20,30,45",
	"test": "2",
	"CREATED_DT": "2021-11-04T11:34:04.6474839+01:00",
	"Id": 1019
}
```

## Send a newsletter

Endpoint: /campaigns/triggerbyjsonwithresult

Data:

```php
$data = [
  'Gate' => 'NEWSLETTER_IMPACTINVESTOR',
  'Gateinput' => [
    'PUBLICATION' => 'IMPACT_INVESTOR',
    'TYPE' => 'WEEKLY'
  ]
];
```

### Example response:

**Success:**

```json
{
	"IsSuccess": true,
	"Error": "",
	"Result": "[ok newsletter generated]"
}
```

## Optin for the newsletter

Endpoint: /campaigns/triggerbyjsonwithresult

Data:

```php
$data = [
  'Gate' => 'OPTIN_IMPACTINVESTOR',
  'Gateinput' => [
    'MAIL' => 'mail@example.com'
  ]
];
```

### Example response:

**Success:**

```json
{
	"IsSuccess": true,
	"Error": "",
	"Result": ""
}
```
