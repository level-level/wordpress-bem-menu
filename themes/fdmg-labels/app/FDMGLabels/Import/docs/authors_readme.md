# Authors

There are two tables containing author info; author and author_details.
## Authors export

```sql
SELECT id, first_name, last_name, full_name
FROM author;
```

There is another table author_details, some authors have overlap with this table. This table has more data; social media handles, email addresses, article counts and  columnist(always NULL or 0)

```sql
SELECT 	a.id AS author_id,
		a.first_name AS first_name,
		a.last_name AS last_name,
		a.full_name AS full_name,
		ad.twitter_username AS twitter_username,
		ad.linkedin_url AS linkedin_url,
		ad.intro_text AS author_intro_text,
		ad.email_address AS author_email_address,
		ad.article_count AS author_article_count,
		ad.columnist AS author_columnist,
		ad.picture_id as picture_id
FROM author AS a
LEFT JOIN author_details AS ad
		ON a.full_name = ad.full_name;
```
