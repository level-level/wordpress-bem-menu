# Short URL's

Short URL's are short-link redirects in the old webwriter website. They are found in the short_url table of the database.

## Export

```sql
SELECT CONCAT('/', url) as source_url, target_url as target_url
FROM short_url
WHERE redirect = 1;
```

## Import

1. Make sure the redirection plugin is active.
2. Create a new redirect group, called "Imported Short URL's".
3. Determine the group ID. You can do so by filtering redirects by your created group, and looking at the url.
4. Run the following command: `wp redirection import CSV_FILE_PATH_HERE --group="GROUP_ID_HERE" --format="csv" --url="SITE_URL"`
