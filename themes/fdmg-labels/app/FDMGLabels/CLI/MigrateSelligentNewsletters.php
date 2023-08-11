<?php

namespace FDMGLabels\CLI;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Extended\ACF\Key;
use FDMGLabels\Connectors\Selligent\Settings\Settings;
use GFFormsModel;
use WP_CLI;

class MigrateSelligentNewsletters {

	/**
	 * Convert Selligent newsletters to support new Selligent structure.
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## Options
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels migrate-selligent-newsletters run
	 */
	public function run( array $args, array $assoc_args ): void {
		$this->process_settings();
		$this->process_newsletters();

		WP_CLI::success( 'Selligent newsletters have been migrated' );
	}

	protected function process_settings(): void {
		WP_CLI::log( 'Migrating settings.' );

		$required_constants = array(
			'II_SELLIGENT_BASE_URL',
			'II_SELLIGENT_USER',
			'II_SELLIGENT_PASS',
			'II_SELLIGENT_LIST_ID_NEWSLETTER',
			'II_SELLIGENT_LIST_ID_ARTICLE',
			'II_SELLIGENT_TYPE',
		);
		foreach ( $required_constants as $required_constant ) {
			if ( defined( $required_constant ) ) {
				continue;
			}

			WP_CLI::log( 'Missing one or more constants to migrate settings.' );
			WP_CLI::log( 'Please make sure to migrate Selligent settings manually via the wp-admin before you continue migrating Selligent newsletters.' );
			WP_CLI::confirm( 'Have you already migrated the Selligent settings manually, and do you want to continue migrating the newsletters?' );
		}

		update_field(
			'selligent', array(
				'base_url'   => constant( 'II_SELLIGENT_BASE_URL' ),
				'username'   => constant( 'II_SELLIGENT_USER' ),
				'password'   => constant( 'II_SELLIGENT_PASS' ),
				'gates'      => array(
					'send_newsletter' => 'NEWSLETTER_IMPACTINVESTOR',
					'optin'           => 'OPTIN_IMPACTINVESTOR',
				),
				'list_ids'   => array(
					'newsletter' => constant( 'II_SELLIGENT_LIST_ID_NEWSLETTER' ),
					'article'    => constant( 'II_SELLIGENT_LIST_ID_ARTICLE' ),
				),
				'newsletter' => array(
					'publications' => array(
						array(
							'name' => 'IMPACT_INVESTOR',
						),
					),
					'types'        => array(
						array(
							'type'           => constant( 'II_SELLIGENT_TYPE' ),
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
	}

	/**
	 * Process newsletters.
	 */
	protected function process_newsletters(): void {
		WP_CLI::log( 'Migrating settings.' );

		/** @var ii_newsletter[] */
		$newsletters = ii_newsletter::get_many(
			array(
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'meta_query'     => array(
					array(
						'key'     => Newsletter::META_PUBLICATION, // This should mean it's not migrated, and is an old version
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		if ( empty( $newsletters ) ) {
			WP_CLI::warning( 'No newsletters found' );
		}

		foreach ( $newsletters as $newsletter ) {
			$this->process_newsletter( $newsletter );
		}
	}

	/**
	 * Process a single newsletter.
	 */
	protected function process_newsletter( ii_newsletter $newsletter ): void {
		WP_CLI::log( sprintf( 'Migrating newsletter %d.', $newsletter->get_id() ) );

		$settings            = Settings::get_instance();
		$newsletter_settings = $settings->get_newsletter();
		$publication         = $newsletter_settings->get_publications()[0] ?? null;
		if ( ! $publication ) {
			WP_CLI::error( 'No publication set. Please configure Selligent.' );
			return;
		}

		$type = $newsletter_settings->get_types()[0] ?? null;
		if ( ! $type ) {
			WP_CLI::error( 'No type set. Please configure Selligent.' );
			return;
		}

		update_field( Newsletter::META_PUBLICATION, $publication->get_name(), $newsletter->get_id() );
		update_field( Newsletter::META_TYPE, $type->get_type(), $newsletter->get_id() );

		$articles_data           = array();
		$acf_article_field_index = 0;
		while ( true ) {
			$value = get_post_meta( $newsletter->get_id(), sprintf( 'articles_%d_article', $acf_article_field_index ), true );
			if ( ! $value ) {
				break;
			}

			$articles_data[] = array(
				Newsletter::META_ARTICLE_GROUP_ARTICLE_ARTICLE => $value,
			);
			$acf_article_field_index++;
		}

		update_field(
			Newsletter::META_ARTICLE_GROUPS_PREFIX . Key::sanitize( $type->get_type() ), array(
				Newsletter::META_ARTICLE_GROUP_PREFIX . '0' => array(
					Newsletter::META_ARTICLE_GROUP_ARTICLES => $articles_data,
				),
			), $newsletter->get_id()
		);
	}
}
