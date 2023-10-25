# Article redirects

The url structure for articles has changed. Therefore, they need to be redirected.

## Export

You need to have an export for articles. To make an export for articles, follow the instructions in the [articles_readme.md](./articles_readme.md)

1. Make sure the redirection plugin is active.
2. Make sure rewrite rules are flushed.
3. Make sure the [articles import](./articles_readme.md) is finished.
4. Run the following command: `wp fdmg-labels import article-redirects run ARTICLES_CSV_FILE_PATH_HERE ARTICLES_REDIRECTS_CSV_FILE_PATH_HERE --url="SITE_URL"`

## Import

1. Create a new redirect group, called "Imported Articles".
2. Determine the group ID. You can do so by filtering redirects by your created group, and looking at the url.
3. Run the following command: `wp redirection import CSV_FILE_PATH_HERE --group="GROUP_ID_HERE" --format="csv" --url="SITE_URL"`
