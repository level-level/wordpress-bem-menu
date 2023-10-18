<?php

namespace FDMGLabels\Connectors\Selligent;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson\Utilities\Image;
use Clarkson_Core\WordPress_Object\attachment;
use Clarkson_Core\WordPress_Object\category;
use Clarkson_Core\WordPress_Object\fdmg_genre;
use Clarkson_Core\WordPress_Object\fdmg_section;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use Codeception\TestCase\WPTestCase;
use DateTime;
use FDMGLabels\Connectors\Selligent\Settings\Settings;

class DataGeneratorAutomaticArticlesTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		// Update settings
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
							),
						),
					),
				),
			), 'options'
		);
		Settings::get_instance()->clear_cache();
	}

	/**
	 * @dataProvider autoPostSelectionProvider
	 */
	public function test_get_newsletter_data_articles_automatic(
		string $sort,
		int $count,
		int $age,
		array $automatic_article_ids
	): void {
		// Create a newsletter
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

		update_field(
			'article_groups_weekly', array(
				'article_group_0' => array(
					'selection_type' => 'automatic',
					'sort'           => $sort,
					'article_count'  => $count,
					'max_age'        => $age,
				),
			), $newsletter->get_id()
		);

		$generated_automatic_article_ids = array_map( 'intval', explode( ',', ( new DataGenerator() )->get_newsletter_data( $newsletter, '1' )['articlegroup_1'] ) );
		$this->assertSame( $automatic_article_ids, $generated_automatic_article_ids );

		wp_delete_post( $newsletter->get_id() );
	}

	/**
	 * @return array<mixed>
	 *
	 * Param order:
	 * 1: Sort
	 * 2: Count
	 * 3: Age in days
	 */
	public function autoPostSelectionProvider(): array {
		// Create terms
		$category = new category(
			$this->factory()->term->create_and_get(
				array(
					'taxonomy' => 'category',
					'name'     => 'Category',
				)
			)
		);
		$section  = new fdmg_section(
			$this->factory()->term->create_and_get(
				array(
					'taxonomy' => 'fdmg_section',
					'name'     => 'Section',
				)
			)
		);
		$genre    = new fdmg_genre(
			$this->factory()->term->create_and_get(
				array(
					'taxonomy' => 'fdmg_genre',
					'name'     => 'Genre',
				)
			)
		);

		// Create posts
		$post_1 = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_status' => 'publish',
					'post_date'   => gmdate( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
				)
			)
		);
		wp_set_post_terms( $post_1->get_id(), array( $category->get_id() ), 'category' );
		wp_set_post_terms( $post_1->get_id(), array( $section->get_id() ), 'fdmg_section' );
		wp_set_post_terms( $post_1->get_id(), array( $genre->get_id() ), 'fdmg_genre' );

		$post_2 = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_status' => 'publish',
					'post_date'   => gmdate( 'Y-m-d H:i:s', strtotime( '-10 days' ) ),
				)
			)
		);

		// Generate data to use in test
		$data = array(
			array( 'recent', PHP_INT_MAX, PHP_INT_MAX, array( $post_1->get_id(), $post_2->get_id() ) ),
			array( 'recent', 1, PHP_INT_MAX, array( $post_1->get_id() ) ),
			array( 'recent', PHP_INT_MAX, 2, array( $post_1->get_id() ) ),
		);
		return $data;
	}
}
