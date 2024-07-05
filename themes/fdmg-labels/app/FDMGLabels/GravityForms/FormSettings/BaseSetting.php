<?php

namespace FDMGLabels\GravityForms\FormSettings;

use GFCommon;

abstract class BaseSetting {
	/** @var mixed */
	protected $default_value = null;

	public function register_hooks(): void {
		add_filter( 'gform_pre_form_settings_save', array( $this, 'pre_settings_save' ), 10, 1 );
		add_filter( 'gform_tooltips', array( $this, 'add_tooltip' ) );
	}

	abstract public function get_type(): string;

	abstract public function get_slug(): string;

	abstract public function get_name(): string;

	public function get_tooltip_slug(): string {
		return sprintf( 'form_setting_%s', $this->get_slug() );
	}

	public function get_tooltip_message(): string {
		return '';
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_choices(): array {
		return array();
	}

	/**
	 * @param array<string,string> $tooltips
	 * @return array<string,string>
	 */
	public function add_tooltip( array $tooltips ): array {
		$tooltip_message = $this->get_tooltip_message();
		if ( empty( $tooltip_message ) ) {
			return $tooltips;
		}

		$tooltips[ $this->get_tooltip_slug() ] = $tooltip_message;
		return $tooltips;
	}

	/**
	 * Gets the data that is required for GravityForms to render this setting
	 *
	 * @param array<string,mixed> $form
	 * @return array<string,mixed>
	 */
	public function get_data( array $form ): array {
		$data = array(
			'name'          => $this->get_slug(),
			'type'          => $this->get_type(),
			'label'         => $this->get_name(),
			'default_value' => array_key_exists( $this->get_slug(), $form ) ? $form[ $this->get_slug() ] : $this->default_value,
		);

		$tooltip = $this->get_tooltip_message();
		if ( $tooltip ) {
			require_once GFCommon::get_base_path() . '/tooltips.php';
			$data['tooltip'] = gform_tooltip( $this->get_tooltip_slug(), '', true );
		}

		$choices = $this->get_choices();
		if ( $choices ) {
			$data['choices'] = array();
			foreach ( $choices as $choice_value => $choice_label ) {
				$data['choices'][] = array(
					'label' => $choice_label,
					'value' => $choice_value,
				);
			}
		}
		return $data;
	}

	/**
	 * @param array<string,mixed> $form
	 * @return mixed
	 */
	public function get_value( array $form ) {
		return Init::get( $form, $this->get_slug(), $this->default_value );
	}

	/**
	 * @param array<string,mixed> $form
	 * @return array<string,mixed>
	 */
	public function pre_settings_save( array $form ): array {
		$form[ $this->get_slug() ] = rgpost( sprintf( '_gform_setting_%s', $this->get_slug() ) );
		return $form;
	}
}
