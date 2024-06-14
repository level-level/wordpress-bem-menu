<?php

namespace FDMGLabels\Connectors\FDMGContentAPI;

use Clarkson_Core\WordPress_Object\attachment;
use Clarkson_Core\WordPress_Object\fdmg_genre;
use Clarkson_Core\WordPress_Object\fdmg_section;
use Clarkson_Core\WordPress_Object\ii_team;
use Clarkson_Core\WordPress_Object\post;
use Clarkson_Core\WordPress_Object\user;
use lucatume\WPBrowser\TestCase\WPTestCase;
use DateTime;

class ObjectConverterTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->object_converter = new ObjectConverter();
	}

	public function test_section(): void {
		$section = new fdmg_section(
			$this->factory()->term->create_and_get(
				array(
					'name'        => 'Test section',
					'taxonomy'    => 'fdmg_section',
					'description' => 'Section description',
					'slug'        => 'test-section',
				)
			)
		);
		$section->update_meta( 'fdmg_import_old_id', 'section_123' );

		$section_model = $this->object_converter->section( $section );
		$this->assertSame( $section->get_id(), $section_model->getId() );
		$this->assertSame( 'Test section', $section_model->getName() );
		$this->assertSame( 'test-section', $section_model->getSlug() );
		$this->assertSame( 'Section description', $section_model->getDescription() );
		$this->assertSame( 'section_123', $section_model->getImportedId() );
	}

	public function test_genre(): void {
		$genre = new fdmg_genre(
			$this->factory()->term->create_and_get(
				array(
					'name'        => 'Test genre',
					'taxonomy'    => 'fdmg_genre',
					'description' => 'Genre description',
					'slug'        => 'test-genre',
				)
			)
		);
		$genre->update_meta( 'fdmg_import_old_id', 'genre_123' );

		$genre_model = $this->object_converter->genre( $genre );
		$this->assertSame( $genre->get_id(), $genre_model->getId() );
		$this->assertSame( 'Test genre', $genre_model->getName() );
		$this->assertSame( 'test-genre', $genre_model->getSlug() );
		$this->assertSame( 'Genre description', $genre_model->getDescription() );
		$this->assertSame( 'genre_123', $genre_model->getImportedId() );
	}

	public function test_user(): void {
		$user = new user(
			$this->factory()->user->create_and_get(
				array(
					'user_login' => 'test-user',
					'user_email' => 'test-user@example.test',
					'first_name' => 'Test',
					'last_name'  => 'User',
				)
			)
		);

		$user_model = $this->object_converter->user( $user );
		$this->assertSame( $user->get_id(), $user_model->getId() );
		$this->assertSame( 'test-user', $user_model->getUsername() );
		$this->assertSame( 'test-user@example.test', $user_model->getEmail() );
		$this->assertSame( 'Test', $user_model->getFirstName() );
		$this->assertSame( 'User', $user_model->getLastName() );
		$this->assertSame( 'Test User', $user_model->getDisplayName() );
		$this->assertSame( 'test-user', $user_model->getNickname() );
	}

	public function test_image(): void {
		// Use different path to test the getDescription function
		$image_paths = array(
			codecept_data_dir() . '/images/test-image-large.png',
			codecept_data_dir() . '/images/canola.jpg',
		);

		foreach ( $image_paths as $index => $image_path ) {
			$image = attachment::get( $this->factory()->attachment->create_upload_object( $image_path ) );
			$image->update_meta( 'fdmg_import_old_id', 'picture_' . $index );

			$image_model = $this->object_converter->image( $image );
			$this->assertSame( $image->get_id(), $image_model->getId() );
			$this->assertEquals( new DateTime( $image->get_object()->post_date, wp_timezone() ), $image_model->getdate() );
			$this->assertEquals( new DateTime( $image->get_object()->post_modified, wp_timezone() ), $image_model->getModified() );
			$this->assertSame( $image->get_post_name(), $image_model->getSlug() );
			$this->assertSame( $image->get_status(), $image_model->getStatus() );
			$this->assertSame( $image->get_post_type(), $image_model->getType() );
			$this->assertSame( $image->get_title(), $image_model->getTitle() );
			$this->assertStringContainsString( $image->get_meta( '_wp_attachment_metadata', true )['file'], $image_model->getDescription() );
			$this->assertSame( $image->get_excerpt(), $image_model->getCaption() );
			$this->assertSame( $image->get_meta( '_wp_attachment_image_alt', true ) ?: '', $image_model->getAltText() );
			$this->assertSame( 'image', $image_model->getMediaType() );
			$this->assertSame( $image->get_object()->post_mime_type, $image_model->getMimeType() );
			$this->assertSame( 'picture_' . $index, $image_model->getImportedId() );

			$media_details_model = $image_model->getMediaDetails();
			$this->assertSame( $image->get_meta( '_wp_attachment_metadata', true )['width'], $media_details_model->getWidth() );
			$this->assertSame( $image->get_meta( '_wp_attachment_metadata', true )['height'], $media_details_model->getHeight() );
			$this->assertSame( $image->get_meta( '_wp_attachment_metadata', true )['file'], $media_details_model->getFile() );
			$this->assertSame( wp_get_attachment_url( $image->get_id() ), $media_details_model->getUrl() );
			$this->assertSame( $image->get_meta( '_wp_attachment_metadata', true )['filesize'], $media_details_model->getFilesize() );
		}
	}

	public function test_article(): void {
		$post = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'     => 'Test article',
					'post_content'   => '<!-- wp:paragraph -->
						<p>Text before paywall</p>
						<!-- /wp:paragraph -->
						<!-- wp:acf/block-ll-paywall-paywall {"name":"acf/block-ll-paywall-paywall","data":{"require_roles_for_access":"0","_require_roles_for_access":"field_5632706c"},"align":"wide","mode":"preview","id":"acf-block-64d6267625850"} /-->
						<!-- wp:paragraph -->
						<p>Text after paywall</p>
						<!-- /wp:paragraph -->',
					'post_excerpt'   => 'Test article excerpt',
					'post_status'    => 'publish',
					'post_type'      => 'post',
					'post_date'      => '2020-01-01 12:00:00',
					'post_name'      => 'test-article',
					'comment_status' => 'open',
					'meta_input'     => array(
						'alternative_title'  => 'Test article alternative title',
						'fdmg_import_old_id' => 'article_123',
					),
				)
			)
		);

		$article_model = $this->object_converter->article( $post );
		$this->assertSame( $post->get_id(), $article_model->getId() );
		$this->assertEquals( new DateTime( $post->get_object()->post_date, wp_timezone() ), $article_model->getdate() );
		$this->assertEquals( new DateTime( $post->get_object()->post_modified, wp_timezone() ), $article_model->getModified() );
		$this->assertSame( $post->get_post_name(), $article_model->getSlug() );
		$this->assertSame( $post->get_status(), $article_model->getStatus() );
		$this->assertSame( $post->get_post_type(), $article_model->getType() );
		$this->assertSame( $post->get_permalink(), $article_model->getLink() );
		$this->assertSame( $post->get_title(), $article_model->getTitle() );
		$this->assertSame( 'Test article alternative title', $article_model->getTeaserTitle() );
		$this->assertSame( $post->get_unpaywalled_content(), $article_model->getContent() );
		$this->assertStringContainsString( 'Text before paywall', $article_model->getContent() );
		$this->assertStringContainsString( 'Text after paywall', $article_model->getContent() );
		$this->assertSame( $post->get_excerpt(), $article_model->getExcerpt() );
		$this->assertSame( 'open', $article_model->getCommentStatus() );
		$this->assertSame( true, $article_model->getPaywall() );
		$this->assertSame( 'article_123', $article_model->getImportedId() );
	}

	public function test_team(): void {
		$team = new ii_team(
			$this->factory()->post->create_and_get(
				array(
					'post_title'   => 'John Doe',
					'post_content' => '<!-- wp:paragraph -->
						<p>John Doe is the main writer for FDMG</p>
						<!-- /wp:paragraph -->',
					'post_excerpt' => 'John Doe writes',
					'post_status'  => 'publish',
					'post_type'    => 'ii_team',
					'post_name'    => 'john-doe',
					'meta_input'   => array(
						'email_address'      => 'johndoe@example.test',
						'linkedin'           => 'https://linkedin.com/in/johndoe',
						'location'           => 'Rotterdam',
						'fdmg_import_old_id' => 'author_123',
					),
				)
			)
		);

		$team_member_model = $this->object_converter->team( $team );
		$this->assertSame( $team->get_id(), $team_member_model->getId() );
		$this->assertSame( 'john-doe', $team_member_model->getSlug() );
		$this->assertSame( 'John Doe', $team_member_model->getName() );
		$this->assertStringContainsString( '<p>John Doe is the main writer for FDMG</p>', $team_member_model->getContent() );
		$this->assertStringContainsString( 'John Doe writes', $team_member_model->getExcerpt() );
		$this->assertSame( 'johndoe@example.test', $team_member_model->getEmail() );
		$this->assertSame( 'https://linkedin.com/in/johndoe', $team_member_model->getLinkedin() );
		$this->assertSame( 'Rotterdam', $team_member_model->getLocation() );
		$this->assertSame( 'author_123', $team_member_model->getImportedId() );
	}
}
