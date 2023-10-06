# Genres

Genres are found in the articles table of the database in the column "prefix". We need to make a csv file of all known Genres for our export.
## Genres export

```sql
SELECT prefix as Genre
FROM article
WHERE prefix IS NOT NULL
    AND prefix != ''
GROUP BY prefix;
```
