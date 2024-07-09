# Article ID redirects

The old website has urls containing the article ID, like https://energeia.nl/12345. These urls now won't work anymore. Therefore, we need to redirect these urls to the new article permalinks.

## Export

1. Make sure the redirection plugin is active.
2. Make sure rewrite rules are flushed.
3. Make sure the [articles import](./articles_readme.md) is finished.
4. Run the following command: `wp fdmg-labels import article-id-redirects run ARTICLES_REDIRECTS_CSV_FILE_PATH_HERE --url="SITE_URL"`

## Import

1. Create a new redirect group, called "Imported Article ID's".
2. Determine the group ID. You can do so by filtering redirects by your created group, and looking at the url.
3. Run the following command: `wp redirection import CSV_FILE_PATH_HERE --group="GROUP_ID_HERE" --format="csv" --url="SITE_URL"`
