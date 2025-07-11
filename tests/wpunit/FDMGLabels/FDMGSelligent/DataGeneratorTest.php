<?php

namespace FDMGLabels\FDMGSelligent;

use Clarkson_Core\WordPress_Object\fdmg_genre;
use Clarkson_Core\WordPress_Object\post;
use FDMGLabels\FeatureManager;
use lucatume\WPBrowser\TestCase\WPTestCase;
use Clarkson_Core\WordPress_Object\attachment;
use DateTimeImmutable;
use LevelLevel\FDMGSelligent\Connectors\Selligent\DataGenerator;
use LevelLevel\FDMGSelligent\Settings\SelligentSettings;
use LevelLevel\FDMGSelligent\Objects\Newsletter as ObjectsNewsletter;

class DataGeneratorTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->feature_manager = new FeatureManager();

		$genre = new fdmg_genre(
			$this->factory()->term->create_and_get(
				array(
					'taxonomy' => 'fdmg_genre',
					'name'     => 'Genre for FDMG Selligent data generator',
				)
			)
		);

		$this->post = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'   => 'Test article selligent',
					'post_type'    => 'post',
					'post_content' => 'Some content here',
					'post_excerpt' => "Some excerpt here\nWith newline\n\nAnd new paragraph",
					'post_status'  => 'publish',
				)
			)
		);
		update_field( 'alternative_title', 'Test teaser title for Test article selligent', $this->post->get_id() );
		$this->thumbnail = attachment::get( $this->factory()->attachment->create_upload_object( codecept_data_dir() . '/images/test-image.png' ) );
		set_post_thumbnail( $this->post->get_id(), $this->thumbnail->get_id() );
		wp_set_post_terms( $this->post->get_id(), array( $genre->get_id() ), 'fdmg_genre' );

		$this->post2 = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'   => 'Test article selligent 2',
					'post_type'    => 'post',
					'post_content' => 'Some content here',
					'post_excerpt' => "Some excerpt here\nWith newline\n\nAnd new paragraph",
					'post_status'  => 'publish',
				)
			)
		);

		update_field(
			'fdmg_selligent', array(
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
		SelligentSettings::get_instance()->clear_cache();
	}

	public function tearDown(): void {
		parent::tearDown();
		delete_field( 'features', 'options' );
		delete_field( 'fdmg_selligent', 'options' );
		wp_delete_post( $this->post->get_id(), true );
		wp_delete_post( $this->post2->get_id(), true );
		wp_delete_post( $this->thumbnail->get_id(), true );
	}

	public function test_get_article_data(): void {
		update_field( 'features', array( 'enable_genre_taxonomy' => true ), 'options' );
		$this->assertTrue( $this->feature_manager->is_enabled( FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY ) );

		$newsletter = new ObjectsNewsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => 'fdmg_sel_newsletter',
					'post_status' => 'publish',
				)
			)
		);

		update_field( 'fdmg_selligent_publication', 'PUBLICATION_NAME', $newsletter->get_post()->ID );

		$expected = array(
			'publication'      => 'PUBLICATION_NAME',
			'articleId'        => $this->post->get_id(),
			'title'            => 'Test teaser title for Test article selligent',
			'teaser'           => 'Some excerpt here<br />With newline<br /><br />And new paragraph',
			'news_section'     => 'Genre for FDMG Selligent data generator',
			'img'              => wp_get_attachment_image_src( $this->thumbnail->get_id(), 'newsletter_large' )[0],
			'img_square'       => wp_get_attachment_image_src( $this->thumbnail->get_id(), 'newsletter_thumbnail' )[0],
			'cta'              => $this->post->get_permalink(),
			'reading_time'     => 1,
			'publication_time' => ( new DateTimeImmutable( $this->post->get_object()->post_date, wp_timezone() ) )->format( 'H:i' ),
		);

		$this->assertSame( $expected, ( new DataGenerator() )->get_article_data( $this->post->get_object(), $newsletter ) );

		wp_delete_post( $newsletter->get_post()->ID, true );
	}

	public function test_get_article_data_field_lengths(): void {
		$newsletter = new ObjectsNewsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => 'fdmg_sel_newsletter',
					'post_status' => 'publish',
				)
			)
		);

		update_field( 'fdmg_selligent_publication', 'PUBLICATION_NAME', $newsletter->get_post()->ID );

		$post = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'   => str_pad( '', 3000, 'A' ),
					'post_type'    => 'post',
					'post_content' => str_pad( '', 3000, 'A' ),
					'post_excerpt' => str_pad( '', 3000, 'A' ),
					'post_status'  => 'publish',
				)
			)
		);

		$generated_data = ( new DataGenerator() )->get_article_data( $post->get_object(), $newsletter );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['publication'] ), 'Publication may not exceed 50 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['title'] ), 'Title may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 2000, strlen( $generated_data['teaser'] ), 'Teaser may not exceed 2000 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['img'] ), 'Image may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['img_square'] ), 'Image square may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['cta'] ), 'CTA may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['news_section'] ), 'News section may not exceed 50 characters' );

		wp_delete_post( $post->get_id(), true );
		wp_delete_post( $newsletter->get_post()->ID, true );
	}

	public function test_get_newsletter_data(): void {
		$newsletter = new ObjectsNewsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => 'fdmg_sel_newsletter',
					'post_status' => 'publish',
				)
			)
		);

		update_field( 'fdmg_selligent_publication', 'PUBLICATION_NAME', $newsletter->get_post()->ID );
		update_field( 'fdmg_selligent_type', 'WEEKLY', $newsletter->get_post()->ID );
		update_field( 'fdmg_selligent_subject', 'Subject', $newsletter->get_post()->ID );
		update_field( 'fdmg_selligent_pre_header', 'Pre header', $newsletter->get_post()->ID );
		update_field( 'fdmg_selligent_introduction', "Some <strong>intro text</strong> with simple <a href=\"#\">elements</a>.\nIt has newlines\n\nand paragraphs.", $newsletter->get_post()->ID );

		update_field(
			'fdmg_selligent_article_groups_weekly', array(
				'article_group_1' => array(
					'articles' => array(
						array( 'article' => $this->post->get_id() ),
						array( 'article' => $this->post2->get_id() ),
					),
				),
			), $newsletter->get_post()->ID
		);

		$expected = array(
			'publication'    => 'PUBLICATION_NAME',
			'type'           => 'WEEKLY',
			'subject'        => 'Subject',
			'preheader'      => 'Pre header',
			'introduction'   => 'Some <strong>intro text</strong> with simple <a href="#">elements</a>.<br />It has newlines<br /><br />and paragraphs.',
			'test'           => '1',
			'articlegroup_1' => '',
			'articlegroup_2' => implode( ',', array( $this->post->get_id(), $this->post2->get_id() ) ),
		);

		$this->assertSame( $expected, ( new DataGenerator() )->get_newsletter_data( $newsletter, '1' ) );

		wp_delete_post( $newsletter->get_post()->ID, true );
	}

	public function test_get_newsletter_data_field_lengths(): void {
		$newsletter = new ObjectsNewsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => str_pad( '', 3000, 'A' ),
					'post_type'   => 'fdmg_sel_newsletter',
					'post_status' => 'publish',
				)
			)
		);

		update_field( 'fdmg_selligent_publication', str_pad( '', 3000, 'A' ), $newsletter->get_post()->ID, $newsletter->get_post()->ID );
		update_field( 'fdmg_selligent_type', str_pad( '', 3000, 'A' ), $newsletter->get_post()->ID, $newsletter->get_post()->ID );
		update_field( 'fdmg_selligent_subject', str_pad( '', 3000, 'A' ), $newsletter->get_post()->ID );
		update_field( 'fdmg_selligent_pre_header', str_pad( '', 3000, 'A' ), $newsletter->get_post()->ID );
		update_field( 'fdmg_selligent_introduction', str_pad( '', 3000, 'A' ), $newsletter->get_post()->ID );
		update_field(
			'fdmg_selligent_articles', array(
				array( 'article' => $this->post->get_id() ),
				array( 'article' => $this->post2->get_id() ),
			), $newsletter->get_post()->ID
		);

		$generated_data = ( new DataGenerator() )->get_newsletter_data( $newsletter, true );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['publication'] ), 'Publication may not exceed 50 characters' );
		$this->assertLessThanOrEqual( 50, strlen( $generated_data['type'] ), 'Type may not exceed 50 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['subject'] ), 'Subject may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 255, strlen( $generated_data['preheader'] ), 'Preheader may not exceed 255 characters' );
		$this->assertLessThanOrEqual( 2000, strlen( $generated_data['introduction'] ), 'Introduction may not exceed 2000 characters' );

		wp_delete_post( $newsletter->get_post()->ID, true );
	}
}
