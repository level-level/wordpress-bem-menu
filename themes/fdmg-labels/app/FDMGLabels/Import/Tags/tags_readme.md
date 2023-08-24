# Tags

Tags have their own separate table, "tag". We need the tag id, tag category id, value, intro_text, enabled and directory.

## Tags export
```
SELECT id, tag_category_id as taxonomy_id, value as term_name, intro_text as description, enabled, directory as slug
FROM tag;
```
Within the tags table we can also find the podcast series, which we need to separate into a new taxonomy. We filter these out, by searching the value, intro_text and the slug  for "podcast".
```
SELECT id, tag_category_id as taxonomy_id, value as term_name, intro_text as description, enabled, directory as slug
FROM tag
WHERE id NOT IN (
    SELECT id
    FROM tag
    WHERE value LIKE '%podcast%'
    OR intro_text LIKE '%podcast%'
    OR directory LIKE '%podcast%'
);
```
