<?php

namespace ImpactInvestor\Connectors\Selligent;

use WordPlate\Acf\Fields\Message;
use WordPlate\Acf\Fields\Relationship;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\Textarea;
use WordPlate\Acf\Fields\TrueFalse;
use WordPlate\Acf\Location;

class Settings {
	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'create_admin_menu' ) );
		add_action( 'acf/init', array( $this, 'register_newsletter_fields' ) );
		add_filter( 'acf/update_value/name=ii_newsletter_send', array( $this, 'send_newsletter' ), 10, 4 );
	}

	public function create_admin_menu(): void {
		acf_add_options_page( array(
			'page_title' => __( 'Impact Investor Newsletter', 'impact-investor' ),
			'menu_title' => __( 'Newsletter', 'impact-investor' ),
			'menu_slug'  => 'impact-investor-newsletter',
			'icon_url'   => 'dashicons-email'      
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
				Message::make( __( 'Creating a newsletter', 'impact-investor' ), 'ii_newsletter_message' )
					->message( __( 'Fill in the required fields and select the posts that need to be included in the newsletter. Press \'Update\' on the right to save the current settings. If the \'Send newsletter\' box is checked, the newsletter will be sent to Selligent. The filled in values are stored in the database, so the same newsletter can be sent again in case of problems.', 'impact-investor' ) ),
				Message::make( __( 'Status', 'impact-investor' ), 'ii_newsletter_status' )
					->message( $this->get_newsletter_status() ),
				Text::make( __( 'Subject', 'impact-investor' ), 'ii_newsletter_subject' )
					->required(),
				Text::make( __( 'Pre header', 'impact-investor' ), 'ii_newsletter_preheader' )
					->required(),
				Textarea::make( __( 'Introduction', 'impact-investor', 'ii_newsletter_introduction' ) )
					->required(),
				Relationship::make( 'Newsletter articles', 'relationship_articles' )
					->instructions( 'Select the articles that will be included in the newsletter' )
					->filters( array(
						'search',
						'post_type',
						'taxonomy'
					) )
					->returnFormat( 'object' )
					->required(),
				TrueFalse::make( __( 'Send newsletter', 'impact-investor' ), 'ii_newsletter_send' )
					->instructions( __( 'Check this box to send the newsletter', 'impact-investor' ) ),
			),
			'location' => array(
				Location::if( 'options_page', 'impact-investor-newsletter' ),
			)
		) );
	}

	/**
	 * Shows information about the last newsletter that has been sent.
	 */
	protected function get_newsletter_status(): string {
		return 'No newsletter has been sent yet.';
	}

	public function send_newsletter( string $value, string $post_id, array $field, string $original ): string {
		if ( false === (bool) $value ) {
			return $value;
		}

		
		return $value;
	}
}
