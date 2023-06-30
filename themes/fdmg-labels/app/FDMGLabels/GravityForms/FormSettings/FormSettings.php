<?php

namespace FDMGLabels\GravityForms\FormSettings;

use FDMGLabels\GravityForms\FormSettings\DonationForm\DonationFrequency;

class FormSettings {

	/** @var BaseSetting[] */
	protected $custom_fields = array();

	public function register_hooks(): void {
		add_filter( 'gform_form_settings_fields', array( $this, 'add_settings_section' ), 10, 2 );

		foreach ( $this->get_custom_fields() as $field ) {
			$field->register_hooks();
		}
	}

	public function add_settings_section( array $fields, array $form ): array {
		$fields['ii_form_settings'] = array(
			'title'  => esc_html( sprintf(
				_x( '%s form settings', 'GravityForms', 'fdmg-labels' ),
				get_bloginfo( 'name' )
			) ),
			'fields' => array_map(
				function( BaseSetting $setting ) use ( $form ): array {
					return $setting->get_data( $form );
				}, $this->get_custom_fields()
			),
		);
		return $fields;
	}

	/**
	 * @return BaseSetting[]
	 */
	public function get_custom_fields(): array {
		if ( ! empty( $this->custom_fields ) ) {
			return $this->custom_fields;
		}

		$this->custom_fields = array(
			new IsSelligentForm(),
		);
		return $this->custom_fields;
	}

	/**
	 * Get a form setting
	 *
	 * @param array $form
	 * @param string $setting
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get( array $form, string $setting, $default = null ) {
		if ( array_key_exists( $setting, $form ) ) {
			return $form[ $setting ];
		}
		return $default;
	}
}
