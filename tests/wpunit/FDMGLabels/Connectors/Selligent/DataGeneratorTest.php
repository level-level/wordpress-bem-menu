<?php

namespace FDMGLabels\Connectors\Selligent;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson\Utilities\Image;
use Clarkson_Core\WordPress_Object\attachment;
use Clarkson_Core\WordPress_Object\fdmg_genre;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use lucatume\WPBrowser\TestCase\WPTestCase;
use DateTime;
use FDMGLabels\Connectors\Selligent\Settings\Settings;
use FDMGLabels\FeatureManager;

class DataGeneratorTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->feature_manager = new FeatureManager();

		$genre = new fdmg_genre(
			$this->factory()->term->create_and_get(
				array(
					'taxonomy' => 'fdmg_genre',
					'name'     => 'Some genre',
				)
			)
		);

		$this->post = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'   => 'Test article selligent',
					'post_type'    => PostTypesPost::POST_TYPE_NAME,
					'post_content' => 'Some content here',
					'post_excerpt' => "Some excerpt here\nWith newline",
					'post_status'  => 'publish',
				)
			)
		);
		update_field( 'alternative_title', 'Test teaser title for Test article selligent', $this->post->get_id() );
		$this->thumbnail = attachment::get( $this->factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/test-image.png' ) );
		set_post_thumbnail( $this->post->get_id(), $this->thumbnail->get_id() );
		wp_set_post_terms( $this->post->get_id(), array( $genre->get_id() ), 'fdmg_genre' );

		$this->post2 = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'   => 'Test article selligent 2',
					'post_type'    => PostTypesPost::POST_TYPE_NAME,
					'post_content' => 'Some content here',
					'post_excerpt' => "Some excerpt here\nWith newline",
					'post_status'  => 'publish',
				)
			)
		);

		update_field(
			'selligent', array(
				'newsletter' => array(
					'publications' => array(
						array(
							'name' => 'PUBLICATION_NAME',
						),
					),
					'types'        => array(
						array(
							'type'           => 'WEEKLY',
							'article_groups' => array(
								array(
									'name' => 'Articles',
								),
								array(
									'name' => 'Podcasts',
								),
							),
						),
					),
				),
			), 'options'
		);
		Settings::get_instance()->clear_cache();
	}

	public function tearDown(): void {
		parent::tearDown();
		delete_field( 'features', 'options' );
		wp_delete_post( $this->post->get_id(), true );
		wp_delete_post( $this->thumbnail->get_id(), true );
	}

	public function test_get_article_data(): void {
		update_field( 'features', array( 'enable_genre_taxonomy' => true ), 'options' );
		$this->assertTrue( $this->feature_manager->is_enabled( FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY ) );

		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		update_field( 'publication', 'PUBLICATION_NAME', $newsletter->get_id() );

		$expected = array(
			'publication'      => 'PUBLICATION_NAME',
			'articleId'        => $this->post->get_id(),
			'title'            => 'Test teaser title for Test article selligent',
			'teaser'           => "<p>Some excerpt here<br />\nWith newline</p>\n",
			'news_section'     => 'Some genre',
			'img'              => wp_get_attachment_image_src( $this->thumbnail->get_id(), 'full' )[0],
			'img_square'       => wp_get_attachment_image_src( $this->thumbnail->get_id(), 'large' )[0],
			'cta'              => $this->post->get_permalink(),
			'reading_time'     => 1,
			'publication_time' => ( new DateTime( $this->post->get_object()->post_date, wp_timezone() ) )->format( 'H:i' ),
		);

		$this->assertSame( $expected, ( new DataGenerator() )->get_article_data( $this->post, $newsletter ) );
	}

	public function test_get_article_data_field_lengths(): void {
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		update_field( 'publication', 'PUBLICATION_NAME', $newsletter->get_id() );

		$post = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'   => str_pad( '', 3000, 'A' ),
					'post_type'    => PostTypesPost::POST_TYPE_NAME,
					'post_content' => str_pad( '', 3000, 'A' ),
					'post_excerpt' => str_pad( '', 3000, 'A' ),
					'post_status'  => 'publish',
				)
			)
		);

		$generated_data = ( new DataGenerator() )->get_article_data( $post, $newsletter );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['publication'] ), 'Publication may not exceed 50 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['title'] ), 'Title may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 2000, strlen( $generated_data['teaser'] ), 'Teaser may not exceed 2000 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['img'] ), 'Image may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['img_square'] ), 'Image square may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['cta'] ), 'CTA may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['news_section'] ), 'News section may not exceed 50 characters' );

		wp_delete_post( $post->get_id(), true );
	}

	public function test_get_newsletter_data(): void {
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		update_field( 'publication', 'PUBLICATION_NAME', $newsletter->get_id() );
		update_field( 'type', 'WEEKLY', $newsletter->get_id() );
		update_field( 'subject', 'Subject', $newsletter->get_id() );
		update_field( 'pre_header', 'Pre header', $newsletter->get_id() );
		update_field( 'introduction', "Some <strong>intro text</strong> with simple <a href=\"#\">elements</a>.\n\nAnd multiple lines.", $newsletter->get_id() );

		update_field(
			'article_groups_weekly', array(
				'article_group_1' => array(
					'articles' => array(
						array( 'article' => $this->post->get_id() ),
						array( 'article' => $this->post2->get_id() ),
					),
				),
			), $newsletter->get_id()
		);

		$expected = array(
			'publication'    => 'PUBLICATION_NAME',
			'type'           => 'WEEKLY',
			'subject'        => 'Subject',
			'preheader'      => 'Pre header',
			'introduction'   => "<p>Some <strong>intro text</strong> with simple <a href=\"#\">elements</a>.</p>\n<p>And multiple lines.</p>\n",
			'test'           => '1',
			'articlegroup_1' => '',
			'articlegroup_2' => implode( ',', array( $this->post->get_id(), $this->post2->get_id() ) ),
		);

		$this->assertSame( $expected, ( new DataGenerator() )->get_newsletter_data( $newsletter, '1' ) );

		wp_delete_post( $newsletter->get_id() );
	}

	public function test_get_newsletter_data_field_lengths(): void {
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => str_pad( '', 3000, 'A' ),
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		update_field( 'publication', str_pad( '', 3000, 'A' ), $newsletter->get_id(), $newsletter->get_id() );
		update_field( 'type', str_pad( '', 3000, 'A' ), $newsletter->get_id(), $newsletter->get_id() );
		update_field( 'subject', str_pad( '', 3000, 'A' ), $newsletter->get_id() );
		update_field( 'pre_header', str_pad( '', 3000, 'A' ), $newsletter->get_id() );
		update_field( 'introduction', str_pad( '', 3000, 'A' ), $newsletter->get_id() );
		update_field(
			'articles', array(
				array( 'article' => $this->post->get_id() ),
				array( 'article' => $this->post2->get_id() ),
			), $newsletter->get_id()
		);

		$generated_data = ( new DataGenerator() )->get_newsletter_data( $newsletter, true );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['publication'] ), 'Publication may not exceed 50 characters' );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['type'] ), 'Type may not exceed 50 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['subject'] ), 'Subject may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['preheader'] ), 'Preheader may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 2000, strlen( $generated_data['introduction'] ), 'Introduction may not exceed 2000 characters' );

		wp_delete_post( $newsletter->get_id() );
	}
}
