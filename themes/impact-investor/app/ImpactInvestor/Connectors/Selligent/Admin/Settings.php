<?php

namespace ImpactInvestor\Connectors\Selligent\Admin;

use WordPlate\Acf\Fields\Message;
use WordPlate\Acf\Fields\Relationship;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;
use WordPlate\Acf\Fields\TrueFalse;
use WordPlate\Acf\Location;

class Settings {
	public const FIELD_MESSAGE   = 'ii_newsletter_message';
	public const FIELD_SUBJECT   = 'ii_newsletter_subject';
	public const FIELD_PREHEADER = 'ii_newsletter_preheader';
	public const FIELD_INTRO     = 'ii_newsletter_introduction';
	public const FIELD_ARTICLES  = 'ii_newsletter_articles';
	public const FIELD_SEND      = 'ii_newsletter_send';
	public const META_STATUS     = 'ii_newsletter_api_status';
	public const META_MESSAGE    = 'ii_newsletter_api_status_message';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'create_admin_menu' ) );
		add_action( 'acf/init', array( $this, 'register_newsletter_fields' ) );
		add_filter( 'acf/update_value/name=' . self::FIELD_SEND, array( $this, 'send_newsletter' ), 10, 4 );

		add_filter( 'acf/fields/relationship/query/name=' . self::FIELD_ARTICLES, array( $this, 'order_articles' ), 10, 3 );
	}

	public function create_admin_menu(): void {
		acf_add_options_page(
			array(
				'page_title'      => __( 'Impact Investor Newsletter', 'impact-investor' ),
				'menu_title'      => __( 'Newsletter', 'impact-investor' ),
				'menu_slug'       => 'impact-investor-newsletter',
				'icon_url'        => 'dashicons-email',
				'updated_message' => __( 'Newsletter has been updated.', 'impact-investor' ),
			)
		);
	}

	/**
	 * Create an ACF field that allows the user to enter all required data for
	 * sending a newsletter.
	 */
	public function register_newsletter_fields(): void {
		register_extended_field_group(
			array(
				'key'      => 'impact-investor-newsletter',
				'title'    => __( 'Impact Investor Newsletter', 'impact-investor' ),
				'fields'   => array(
					Message::make( __( 'Creating a newsletter', 'impact-investor' ), self::FIELD_MESSAGE )
						->message( __( 'Fill in the required fields and select the posts that need to be included in the newsletter. Press \'Update\' on the right to save the current settings. If the \'Send newsletter\' box is checked, the newsletter will be sent to Selligent. The filled in values are stored in the database, so the same newsletter can be sent again in case of problems.', 'impact-investor' ) ),
					Text::make( __( 'Subject', 'impact-investor' ), self::FIELD_SUBJECT )
						->required(),
					Text::make( __( 'Pre header', 'impact-investor' ), self::FIELD_PREHEADER )
						->required(),
					Textarea::make( __( 'Introduction', 'impact-investor' ), self::FIELD_INTRO )
						->newLines( 'br' )
						->required(),
					Relationship::make( 'Newsletter articles', self::FIELD_ARTICLES )
					->instructions( 'Select the articles that will be included in the newsletter' )
					->postTypes( array( 'post' ) )
					->filters(
						array(
							'search',
							'taxonomy',
						)
					)
					->returnFormat( 'id' )
					->required(),
					TrueFalse::make( __( 'Send newsletter', 'impact-investor' ), self::FIELD_SEND )
							->instructions( __( 'Check this box to send the newsletter after pressing \'Update\'.', 'impact-investor' ) )
							->stylisedUi(),
				),
				'location' => array(
					Location::if( 'options_page', 'impact-investor-newsletter' ),
				),
			)
		);
	}

	/**
	 * If the 'Send newsletter' box has been checked, send the data to
	 * Selligent. Returns false to reset the box to an empty state.
	 */
	public function send_newsletter( string $value, string $post_id, array $field, string $original ): string {
		if ( false === (bool) $value ) {
			return $value;
		}

		$articles = get_field( self::FIELD_ARTICLES, 'options' );

		if ( is_array( $articles ) && ! empty( $articles ) ) {
			wp_schedule_single_event( time(), 'impact_investor_send_newsletter', array( $articles ) );
			update_option(
				self::META_MESSAGE, sprintf(
					__( '[%1$s] A newsletter will be created with %2$d articles.', 'impact-investor' ),
					date_i18n( 'Y-m-d H:i:s' ),
					count( $articles )
				)
			);
		}

		return '0';
	}

	/**
	 * Order the relationship field articles by ID (DESC) to make sure the most
	 * recent articles are shown on top.
	 */
	public function order_articles( array $args, array $field, string $post_id ): array {
		$args['orderby'] = 'ID';
		$args['order']   = 'DESC';
		return $args;
	}
}
