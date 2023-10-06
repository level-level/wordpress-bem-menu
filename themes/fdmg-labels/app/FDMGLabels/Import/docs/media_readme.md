# Media

The database contains PDFs and images. PDFs can be found in "binary_file" table. Images can be found in "picture" table.
## PDF files export
Binary files are always of type PDF, so we import them all.
```
SELECT id, state, filename
FROM binary_file;
```
## Image files export
Select all images
```
SELECT id, caption, state, photographer, title, path, using_imgix
FROM picture
WHERE state != 'DELETED';
```
