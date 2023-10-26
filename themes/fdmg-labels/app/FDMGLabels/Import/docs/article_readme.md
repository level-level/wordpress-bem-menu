# Articles

## Export
```sql
SELECT article.id,
	article.state,
	article.publication_date,
	article.last_modified_date,
	article.prefix,
	article.title,
	article.publication_url,
	article.disallow_comments,
	article.do_not_index,
	article.taxonomy,
	article.free,
 	article_content.content,
	article_content.intro AS content_intro,
	article_content.teaser_intro AS excerpt,
	article_picture.picture_id as post_thumbnail,
	(SELECT GROUP_CONCAT(author_id) from article_author where article_id = article.id) as author_ids,
	(SELECT GROUP_CONCAT(tag_id) from article_tag where article_id = article.id ) as tag_ids,
	(SELECT GROUP_CONCAT(section_name) from article_section where article_id = article.id ) as section_names
FROM article
LEFT JOIN article_content
	ON article.id = article_content.article_id
LEFT JOIN article_picture
	ON article.id = article_picture.article_id
	 AND article_picture.picture_type = 'OVERVIEW'
WHERE article.state != 'DELETED'
AND article.state != 'APPROVED'
AND article.article_type = 'ARTICLE'
ORDER BY article.id
```

## import
1. Export the articles in batches.
2. Create a new import using the import template
3. run the import
