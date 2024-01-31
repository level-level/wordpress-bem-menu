# Sections

Select all from "sections" table.

## Import notes

fdmg*import_old_id is prefixed with 'section*'

## Export

Run the following SQL query, and convert the result to CSV.

```sql
SELECT *
FROM section
WHERE name NOT LIKE '%config%'
AND full_path IS NOT NULL;
```

## Import

1. Upload the generated CSV file, to create an import for the Section taxonomy.
2. Please use the [template file](./../WPAllImport/templates/import-sections.txt) to automatically apply all the required import configuration.
3. Make sure that in settings, "Increase speed by disabling do_action calls in wp_insert_post during import." is enabled.
4. Run the import.
5. Run the import again to restore relations to their parent terms.
