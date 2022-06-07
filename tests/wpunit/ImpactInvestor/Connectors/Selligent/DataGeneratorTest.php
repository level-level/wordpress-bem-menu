<?php

namespace ImpactInvestor\Connectors\Selligent;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson\Utilities\Image;
use Clarkson_Core\WordPress_Object\attachment;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use Codeception\TestCase\WPTestCase;

class DataGeneratorTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->post      = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'   => 'Test tip selligent',
					'post_type'    => PostTypesPost::POST_TYPE_NAME,
					'post_content' => 'Some content here',
					'post_excerpt' => "Some excerpt here\nWith newline",
					'post_status'  => 'publish',
				)
			)
		);
		$this->thumbnail = attachment::get( $this->factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/test-image.png' ) );
		set_post_thumbnail( $this->post->get_id(), $this->thumbnail->get_id() );

		$this->post2 = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'   => 'Test tip selligent',
					'post_type'    => PostTypesPost::POST_TYPE_NAME,
					'post_content' => 'Some content here',
					'post_excerpt' => "Some excerpt here\nWith newline",
					'post_status'  => 'publish',
				)
			)
		);
	}

	public function tearDown(): void {
		parent::tearDown();
		wp_delete_post( $this->post->get_id(), true );
		wp_delete_post( $this->thumbnail->get_id(), true );
	}

	public function test_get_article_data(): void {
		$expected = array(
			'publication'  => Client::PUBLICATION,
			'articleId'    => $this->post->get_id(),
			'title'        => 'Test tip selligent',
			'teaser'       => "<p>Some excerpt here<br />\nWith newline</p>\n",
			'news_section' => '',
			'img'          => wp_get_attachment_image_url( $this->thumbnail->get_id(), 'post-thumbnail' ),
			'cta'          => $this->post->get_permalink(),
		);

		$this->assertSame( $expected, DataGenerator::get_article_data( $this->post ) );
	}

	public function test_get_article_data_field_lengths(): void {
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

		$generated_data = DataGenerator::get_article_data( $post );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['publication'] ), 'Publication may not exceed 50 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['title'] ), 'Title may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 2000, strlen( $generated_data['teaser'] ), 'Teaser may not exceed 2000 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['img'] ), 'Image may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['cta'] ), 'CTA may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['news_section'] ), 'News section may not exceed 50 characters' );

		wp_delete_post( $post->get_id(), true );
	}

	public function test_get_newsletter_data_testmode(): void {
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		update_field( Newsletter::META_SUBJECT, 'Subject', $newsletter->get_id() );
		update_field( Newsletter::META_PRE_HEADER, 'Pre header', $newsletter->get_id() );
		update_field( Newsletter::META_INTRODUCTION, "Some <strong>intro text</strong> with simple <a href=\"#\">elements</a>.\n\nAnd multiple lines.", $newsletter->get_id() );
		update_field(
			Newsletter::META_ARTICLES, array(
				array( Newsletter::META_ARTICLES_ARTICLE => $this->post->get_id() ),
				array( Newsletter::META_ARTICLES_ARTICLE => $this->post2->get_id() ),
			), $newsletter->get_id()
		);

		$expected = array(
			'publication'  => Client::PUBLICATION,
			'type'         => Client::get_type(),
			'subject'      => 'Subject',
			'preheader'    => 'Pre header',
			'introduction' => "<p>Some <strong>intro text</strong> with simple <a href=\"#\">elements</a>.</p>\n<p>And multiple lines.</p>\n",
			'test'         => '1',
			'article_1'    => $this->post->get_id(),
			'article_2'    => $this->post2->get_id(),
		);

		$this->assertSame( $expected, DataGenerator::get_newsletter_data( $newsletter, true ) );

		wp_delete_post( $newsletter->get_id() );
	}

	public function test_get_newsletter_data_prod(): void {
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		update_field( Newsletter::META_SUBJECT, 'Subject', $newsletter->get_id() );
		update_field( Newsletter::META_PRE_HEADER, 'Pre header', $newsletter->get_id() );
		update_field( Newsletter::META_INTRODUCTION, "Some <strong>intro text</strong> with simple <a href=\"#\">elements</a>.\n\nAnd multiple lines.", $newsletter->get_id() );
		update_field(
			Newsletter::META_ARTICLES, array(
				array( Newsletter::META_ARTICLES_ARTICLE => $this->post->get_id() ),
				array( Newsletter::META_ARTICLES_ARTICLE => $this->post2->get_id() ),
			), $newsletter->get_id()
		);

		$expected = array(
			'publication'  => Client::PUBLICATION,
			'type'         => Client::get_type(),
			'subject'      => 'Subject',
			'preheader'    => 'Pre header',
			'introduction' => "<p>Some <strong>intro text</strong> with simple <a href=\"#\">elements</a>.</p>\n<p>And multiple lines.</p>\n",
			'test'         => '0',
			'article_1'    => $this->post->get_id(),
			'article_2'    => $this->post2->get_id(),
		);

		$this->assertSame( $expected, DataGenerator::get_newsletter_data( $newsletter, false ) );

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

		update_field( Newsletter::META_SUBJECT, str_pad( '', 3000, 'A' ), $newsletter->get_id() );
		update_field( Newsletter::META_PRE_HEADER, str_pad( '', 3000, 'A' ), $newsletter->get_id() );
		update_field( Newsletter::META_INTRODUCTION, str_pad( '', 3000, 'A' ), $newsletter->get_id() );
		update_field(
			Newsletter::META_ARTICLES, array(
				array( Newsletter::META_ARTICLES_ARTICLE => $this->post->get_id() ),
				array( Newsletter::META_ARTICLES_ARTICLE => $this->post2->get_id() ),
			), $newsletter->get_id()
		);

		$generated_data = DataGenerator::get_newsletter_data( $newsletter, true );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['publication'] ), 'Publication may not exceed 50 characters' );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['type'] ), 'Type may not exceed 50 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['subject'] ), 'Subject may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['preheader'] ), 'Preheader may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 2000, strlen( $generated_data['introduction'] ), 'Introduction may not exceed 2000 characters' );

		wp_delete_post( $newsletter->get_id() );
	}
}
