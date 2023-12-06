# Authors

There are two tables containing author info; author and author_details.

## Import notes
fdmg_import_old_id is prefixed with 'author_'

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
		ad.picture_id as picture_id,
		ad.directory as directory
FROM author AS a
LEFT JOIN author_details AS ad
		ON a.full_name = ad.full_name;
```

## Import
1. Make sure the pictures/images are imported as attachments.
2. Upload the generated CSV file, to create an import for team / authors
3. Please use the template file to automatically apply the required import configuration
4. Make sure that in settings, "Increase speed by disabling do_action calls in wp_insert_post during import." is enabled.
5. Run the import.
