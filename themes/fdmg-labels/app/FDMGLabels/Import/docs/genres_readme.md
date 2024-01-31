# Genres

Genres are found in the articles table of the database in the column "prefix". We need to make a csv file of all known Genres for our export.

## Import notes

fdmg*import_old_id is prefixed with 'genre*'

## Export

Run the following SQL query, and convert the result to CSV.

```sql
SELECT prefix as Genre
FROM article
WHERE prefix IS NOT NULL
    AND prefix != ''
GROUP BY prefix;
```

## Import

1. Upload the generated CSV file, to create an import for the Genre taxonomy.
2. Please use the [template file](./../WPAllImport/templates/import-genres.txt) to automatically apply all the required import configuration.
3. Make sure that in settings, "Increase speed by disabling do_action calls in wp_insert_post during import." is enabled.
4. Run the import.
