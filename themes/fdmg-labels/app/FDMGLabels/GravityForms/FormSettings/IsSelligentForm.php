<?php

namespace FDMGLabels\GravityForms\FormSettings;

use FDMGLabels\GravityForms\FormSettings\BaseSetting;

final class IsSelligentForm extends BaseSetting {
	protected $default_value = false;

	public function get_type(): string {
		return 'toggle';
	}

	public function get_slug(): string {
		return 'ii_is_selligent_form';
	}

	public function get_name(): string {
		return _x( 'Is Selligent form', 'GravityForms', 'fdmg-labels' );
	}

	public function get_tooltip_message(): string {
		return _x( 'Enable Selligent functionalities. Form entries will be sent to Selligent as a newsletter subscription.', 'GravityForms', 'fdmg-labels' );
	}

	public function pre_settings_save( array $form ): array {
		$value                     = rgpost( sprintf( '_gform_setting_%s', $this->get_slug() ) );
		$value                     = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		$form[ $this->get_slug() ] = $value;
		return $form;
	}

	/**
	 * Get the HTML for rendering this field
	 */
	public function get_description(): string {
		return sprintf(
			_x( 'To use this functionality, a form field of the type "%s" must be present in the form.', 'GravityForms', 'fdmg-labels' ),
			_x( 'Email', 'GravityForms', 'gravityforms' )
		);
	}
}
