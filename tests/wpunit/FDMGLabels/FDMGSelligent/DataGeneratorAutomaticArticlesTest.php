<?php

namespace FDMGLabels\FDMGSelligent;

use Clarkson_Core\WordPress_Object\category;
use Clarkson_Core\WordPress_Object\fdmg_genre;
use Clarkson_Core\WordPress_Object\fdmg_section;
use Clarkson_Core\WordPress_Object\post;
use LevelLevel\FDMGSelligent\Connectors\Selligent\DataGenerator;
use LevelLevel\FDMGSelligent\Objects\Newsletter as ObjectsNewsletter;
use LevelLevel\FDMGSelligent\Settings\SelligentSettings;
use lucatume\WPBrowser\TestCase\WPTestCase;

class DataGeneratorAutomaticArticlesTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		// Update settings
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
							),
						),
					),
				),
			), 'options'
		);
		SelligentSettings::get_instance()->clear_cache();
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

		update_field(
			'fdmg_selligent_article_groups_weekly', array(
				'article_group_0' => array(
					'selection_type' => 'automatic',
					'sort'           => $sort,
					'article_count'  => $count,
					'max_age'        => $age,
				),
			), $newsletter->get_post()->ID
		);

		$generated_automatic_article_ids = array_map( 'intval', explode( ',', ( new DataGenerator() )->get_newsletter_data( $newsletter, '1' )['articlegroup_1'] ) );
		$this->assertSame( $automatic_article_ids, $generated_automatic_article_ids );

		wp_delete_post( $newsletter->get_post()->ID );
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
					'name'     => 'Category for FDMG Selligent data generator auto post selection',
				)
			)
		);
		$section  = new fdmg_section(
			$this->factory()->term->create_and_get(
				array(
					'taxonomy' => 'fdmg_section',
					'name'     => 'Section for FDMG Selligent data generator auto post selection',
				)
			)
		);
		$genre    = new fdmg_genre(
			$this->factory()->term->create_and_get(
				array(
					'taxonomy' => 'fdmg_genre',
					'name'     => 'Genre for FDMG Selligent data generator auto post selection',
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

		$data = array(
			array( 'recent', 9999, 9999, array( $post_1->get_id(), $post_2->get_id() ) ),
			array( 'recent', 1, 9999, array( $post_1->get_id() ) ),
			array( 'recent', 9999, 2, array( $post_1->get_id() ) ),
		);
		return $data;
	}
}
