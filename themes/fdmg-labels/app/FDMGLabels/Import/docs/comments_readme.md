# Comments

Users have the possibility to write comments for an article.

## Import notes
fdmg_import_old_id is prefixed with 'comment_'

## Export

Make sure you have downloaded the fdmg-comments.csv and fdmg-customers.csv to your local machine.
Then, run `wp fdmg-labels import generate-comments-csv run /path/to/fdmg-comments.csv /path/to/fdmg-customers.csv /path/to/generated-comments.csv  --url="SITE_URL"`

## Import

1. Upload the generated CSV file, to create an import for comments.
2. Please use the [template file](./../WPAllImport/templates/import-comments.txt) to automatically apply all the required import configuration.
3. Make sure that in settings, "Increase speed by disabling do_action calls in wp_insert_post during import." is enabled.
4. Run the import.
