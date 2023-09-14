<?php
namespace Clarkson\WPadmin;

use Clarkson_Core\Templates;

class Plugins {
	public function register_hooks(): void {
		add_filter( 'wpseo_metabox_prio', array( $this, 'set_prio_yoast_metabox' ) );

		// Remove Yoast version number by default
		add_filter( 'wpseo_hide_version', '__return_true' );

		// Hide ACF admin interface, use Extended ACF. See https://github.com/vinkla/extended-acf
		add_filter( 'acf/settings/show_admin', '__return_false' );

		// Used to change the tabindex start value or to disable the tabindex attribute for gravity forms
		add_filter( 'gform_tabindex', '__return_false' );

		// Disable gravity forms theme styling, only use basic.css
		add_filter( 'gform_disable_form_theme_css', '__return_true' );

		// Force output with HTML5 elements in Gravity Forms
		add_filter( 'pre_option_rg_gforms_enable_html5', '__return_true' );

		// Disable the 'No-Conflict Mode' option in Gravity Forms
		add_filter( 'pre_option_gform_enable_noconflict', '__return_false' );

		// Add a custom class around every gravity forms entity
		add_filter( 'gform_get_form_filter', array( $this, 'add_custom_wrapper' ) );

		// Custom the GF submit button HTML output
		add_filter( 'gform_submit_button', array( $this, 'edit_form_button_html' ), 10, 2 );

		// Set default GF options
		add_filter( 'gform_form_settings_initial_values', array( $this, 'default_gravity_forms_settings' ) );
	}

	/**
	 * Move Yoast SEO metabox to bottom
	 */
	public function set_prio_yoast_metabox(): string {
		return 'low';
	}

	/**
	 * Adds a custom wrapper around any gravity form
	 */
	public function add_custom_wrapper( string $form_string ): string {
		$form_string_new  = '<div class="gravity-forms">';
		$form_string_new .= $form_string;
		$form_string_new .= '</div>';

		return $form_string_new;
	}

	public function edit_form_button_html( string $button, array $form ): string {
		return Templates::get_instance()->render_twig(
			'styleguide/components/buttons/button-element.twig',
			array(
				'id'    => 'gform_submit_button_' . $form['id'],
				'text'  => $form['button']['text'],
				'class' => 'btn-primary',
			),
			true
		);
	}

	public function default_gravity_forms_settings( array $initial_values ): array {
		$initial_values['validationSummary']    = true;
		$initial_values['labelPlacement']       = 'top_label';
		$initial_values['descriptionPlacement'] = 'above';

		return $initial_values;
	}
}
