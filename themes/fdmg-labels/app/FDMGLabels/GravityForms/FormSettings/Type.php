<?php

namespace FDMGLabels\GravityForms\FormSettings;

use FDMGLabels\GravityForms\FormSettings\BaseSetting;

final class Type extends BaseSetting {

	public const TYPE_DEFAULT    = 'default';
	public const TYPE_NEWSLETTER = 'newsletter';
	public const TYPE_DOWNLOAD   = 'download';

	public function get_type(): string {
		return 'select';
	}

	public function get_slug(): string {
		return 'fdmg_form_type';
	}

	public function get_name(): string {
		return __( 'Form Type', 'fdmg-labels-admin' );
	}

	/**
	 * @return array<mixed>
	 */
	protected function get_choices(): array {
		return array_merge(
			array(
				self::TYPE_DEFAULT    => __( 'Standard form', 'fdmg-labels-admin' ),
				self::TYPE_NEWSLETTER => __( 'Newsletter form', 'fdmg-labels-admin' ),
				self::TYPE_DOWNLOAD   => __( 'Article download', 'fdmg-labels-admin' ),
			),
		);
	}
}
