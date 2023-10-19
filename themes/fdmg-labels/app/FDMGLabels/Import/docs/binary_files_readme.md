# Binary files

The database contains pdf files. They can be found in the "binary files" table.

## Import notes
fdmg_import_old_id is prefixed with 'binary_file_'

## Export
Select all binary files

```sql
SELECT bf.id, bf.state, bf.title, bf.filename as path, bf.binary_type
FROM binary_file as bf
WHERE bf.state = 'PUBLISHED';
```

## Import

1. Run the [python script](./../python-scripts/binary_files_download.py) to download the pdf files to the `wp-content/uploads/sites/ID/imported-binary-files` directory. Make sure to modify database settings and storage location.
2. Upload the generated CSV file, to create an import for attachments.
3. Please use the [template file](./../WPAllImport/templates/import-binary-files.txt) to automatically apply all the required import configuration.
4. Make sure that in settings, "Increase speed by disabling do_action calls in wp_insert_post during import." is enabled.
5. Run the import.
