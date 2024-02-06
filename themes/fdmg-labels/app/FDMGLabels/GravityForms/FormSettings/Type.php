<?php

namespace FDMGLabels\GravityForms\FormSettings;

use FDMGLabels\GravityForms\FormSettings\BaseSetting;

final class Type extends BaseSetting {
	public function get_type(): string {
		return 'select';
	}

	public function get_slug(): string {
		return 'fdmg_form_type';
	}

	public function get_name(): string {
		return __( 'Formulier Type', 'fdmg-labels-admin' );
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_choices(): array {
		return array_merge(
			array(
				'default'    => __( 'Standaard formulier', 'fdmg-labels-admin' ),
				'newsletter' => __( 'Newsletter formulier', 'fdmg-labels-admin' ),
				'download'   => __( 'Artikel download', 'fdmg-labels-admin' ),
			),
		);
	}
}
