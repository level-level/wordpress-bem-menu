# Pictures

The database contains images. Images can be found in "picture" table.

## Import notes
fdmg_import_old_id is prefixed with 'picture_'

## Image files export
Select all images

```sql
SELECT p.id, p.caption, p.state, p.photographer, p.title, p.path, p.using_imgix
, ap.article_id, a.id as author_id FROM picture as p
LEFT JOIN article_picture as ap ON p.id = ap.picture_id
LEFT JOIN author_details as ad ON p.id = ad.picture_id
LEFT JOIN author as a ON ad.full_name = a.full_name
WHERE p.state != 'DELETED';
```

## Import

1. Upload the generated CSV file, to create an import for attachments.
2. Please use the [template file](./../WPAllImport/templates/import-pictures.txt) to automatically apply all the required import configuration.
3. Make sure that in settings, "Increase speed by disabling do_action calls in wp_insert_post during import." is enabled.
4. Run the import.
