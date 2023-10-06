# Sections

Select all from "sections" table.
## Sections export
```
SELECT *
FROM section;
```
Select all rows without fields containing 'config'.
```
SELECT *
FROM section
WHERE name NOT LIKE '%config%';
```
