# Tags

Tags have their own separate table, "tag". We need the tag id, tag category id, value, intro_text, enabled and directory.

## Import notes
fdmg_import_old_id is prefixed with 'tag_'

## Export

```sql
SELECT id, tag_category_id as taxonomy_id, value as term_name, intro_text as description, enabled, directory as slug
FROM tag;
```

## Import
1. Upload the generated CSV file, to create an import for the Category taxonomy.
2. Please use the [template file](./../WPAllImport/templates/import-tags.txt) to automatically apply all the required import configuration.
3. Make sure that in settings, "Increase speed by disabling do_action calls in wp_insert_post during import." is enabled.
4. Run the import.
