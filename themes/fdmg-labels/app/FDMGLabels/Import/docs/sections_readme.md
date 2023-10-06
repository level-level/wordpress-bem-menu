# Sections

Select all from "sections" table.
## Sections export

```sql
SELECT *
FROM section;
```

Select all rows without fields containing 'config'.

```sql
SELECT *
FROM section
WHERE name NOT LIKE '%config%';
```
