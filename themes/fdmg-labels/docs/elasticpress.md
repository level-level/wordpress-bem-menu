# Elasticpress
This project uses [ElasticPress](https://nl.wordpress.org/plugins/elasticpress/) (and therefore [ElasticSearch](https://www.elastic.co/)) to provide better search and related content functionalities.

## Configure ElasticPress
1. Network-enable the ElasticPress, Query Monitor, Debug bar and Debug Bar ElasticPress plugins.
2. Add the localhost url via the `ElasticPress settings -> Third-Party/Self-Hosted -> Elasticsearch Host URL` (via wp-admin/admin.php?page=elasticpress-settings). If an error message appears, refresh the settings page.
3. Start the sync to index all posts by clicking the sync icon at the top right. You can also run the following wp-cli command: `wp elasticpress sync`
4. Do a search request and check with `Query monitor -> Debug Bar: ElasticPress` if the query request comes from the Elasticsearch server.

See [ElasticPress docs](https://10up.github.io/ElasticPress/index.html) for available actions and filters.

## Setup a local Elasticsearch server
For testing elasticsearch locally, you will need to run an ElasticSearch server.

To setup an Elasticsearch server with docker, run in your terminal:
```bash
docker run -d --name elasticsearch-fdmg-labels -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" -e "xpack.security.enabled=false" elasticsearch:7.17.14
```

Test if the Elasticsearch server is running by visiting this url in the browser: `http://localhost:9200/`.
It could take a few minutes before the server is booted.
