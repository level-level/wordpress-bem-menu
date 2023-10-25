# Author redirects

The url structure for authors has changed. Therefore, they need to be redirected.

## Export

You need to have an export for authors. To make an export for authors, follow the instructions in the [authors_readme.md](./authors_readme.md).

1. Make sure the redirection plugin is active.
2. Make sure you have configured a translated slug for the author/team post type.
3. Make sure rewrite rules are flushed.
4. Make sure the [authors import](./authors_readme.md) is finished.
5. Run the following command: `wp fdmg-labels import author-redirects run AUTHORS_CSV_FILE_PATH_HERE AUTHOR_REDIRECTS_CSV_FILE_PATH_HERE --url="SITE_URL"`

## Import
1. Create a new redirect group, called "Imported Authors".
2. Determine the group ID. You can do so by filtering redirects by your created group, and looking at the url.
3. Run the following command: `wp redirection import CSV_FILE_PATH_HERE --group="GROUP_ID_HERE" --format="csv" --url="SITE_URL"`
