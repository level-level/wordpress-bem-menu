<?php

namespace ImpactInvestor\Connectors\Selligent\Admin;

use Clarkson_Core\WordPress_Object\post;
use ImpactInvestor\Connectors\Selligent\Client;
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

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'create_admin_menu' ) );
		add_action( 'acf/init', array( $this, 'register_newsletter_fields' ) );
		add_filter( 'acf/update_value/name=ii_newsletter_send', array( $this, 'send_newsletter' ), 10, 4 );

		add_filter( 'acf/fields/relationship/query/name=' . self::FIELD_ARTICLES, array( $this, 'order_articles' ), 10, 3 );
	}

	public function create_admin_menu(): void {
		$updated_message = __( 'Newsletter has been updated, but has not been sent.', 'impact-investor' );
		if ( get_field( self::FIELD_SEND, 'options' ) ) {
			$updated_message = __( 'Newsletter has been updated and has been sent to Selligent.', 'impact-investor' );
		}
		acf_add_options_page( array(
			'page_title' => __( 'Impact Investor Newsletter', 'impact-investor' ),
			'menu_title' => __( 'Newsletter', 'impact-investor' ),
			'menu_slug'  => 'impact-investor-newsletter',
			'icon_url'   => 'dashicons-email',
			'updated_message' => __( 'Newsletter has been updated.', 'impact-investor' ),
		) );
	}

	/**
	 * Create an ACF field that allows the user to enter all required data for
	 * sending a newsletter.
	 */
	public function register_newsletter_fields(): void {
		register_extended_field_group( array(
			'key'   => 'impact-investor-newsletter',
			'title' => __( 'Impact Investor Newsletter', 'impact-investor' ),
			'fields' => array(
				Message::make( __( 'Creating a newsletter', 'impact-investor' ), self::FIELD_MESSAGE )
					->message( __( 'Fill in the required fields and select the posts that need to be included in the newsletter. Press \'Update\' on the right to save the current settings. If the \'Send newsletter\' box is checked, the newsletter will be sent to Selligent. The filled in values are stored in the database, so the same newsletter can be sent again in case of problems.', 'impact-investor' ) ),
				Text::make( __( 'Subject', 'impact-investor' ), self::FIELD_SUBJECT )
					->required(),
				Text::make( __( 'Pre header', 'impact-investor' ), self::FIELD_PREHEADER )
					->required(),
				Textarea::make( __( 'Introduction', 'impact-investor', self::FIELD_INTRO ) )
					->required(),
				Relationship::make( 'Newsletter articles', self::FIELD_ARTICLES )
					->instructions( 'Select the articles that will be included in the newsletter' )
					->postTypes( array( 'post' ) )
					->filters( array(
						'search',
						'taxonomy'
					) )
					->returnFormat( 'object' )
					->required(),
				TrueFalse::make( __( 'Send newsletter', 'impact-investor' ), self::FIELD_SEND )
					->instructions( __( 'Check this box to send the newsletter after pressing \'Update\'.', 'impact-investor' ) ),
			),
			'location' => array(
				Location::if( 'options_page', 'impact-investor-newsletter' ),
			)
		) );
	}

	/**
	 * If the 'Send newsletter' box has been checked, send the data to
	 * Selligent. Returns false to reset the box to an empty state.
	 */
	public function send_newsletter( string $value, string $post_id, array $field, string $original ): string {
		if ( false === (bool) $value ) {
			return $value;
		}

		$client = new Client();

		/** @var post[] $articles */
		$articles = get_field( self::FIELD_ARTICLES, 'options' );
		$post_ids = array();

		$status = array(
			'time'       => time(),
			'articles'   => array(),
			'newsletter' => null,
		);

		foreach( $articles as $article ) {
			$registration_result  = $client->register_post( $article );
			$status['articles'][] = $this->get_status( $registration_result );
			$post_ids[]           = $article->get_id();
		}

		$newsletter_result    = $client->register_newsletter( $post_ids );
		$status['newsletter'] = $this->get_status( $newsletter_result );

		update_option( self::META_STATUS, $status );

		return false;
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

	/**
	 * Returns the status in a format that can be saved in the databse. If the
	 * returned body is JSON, decode that to prevent issues with serialization.
	 * 
	 * @return array<string, string>
	 */
	public function get_status( array $api_response ): array {
		$body = json_decode( $api_response['body'] );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$body = $api_response['body'];
		}

		return array(
			'code'    => $api_response['response']['code'],
			'message' => $api_response['response']['message'],
			'body'    => $body,
		);
	}
}
