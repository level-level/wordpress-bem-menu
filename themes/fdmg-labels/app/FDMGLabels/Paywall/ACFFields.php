<?php

namespace FDMGLabels\Paywall;

use Clarkson\WPadmin\Capabilities;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Field;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Key;
use LevelLevel\Paywall\ACFBlock;

class ACFFields {

	public const META_REQUIRE_ROLES_FOR_ACCESS = 'require_roles_for_access';
	public const META_ALLOWED_USER_ROLES       = 'allowed_user_roles';

	public function register_hooks(): void {
		add_filter( 'll_paywall_paywall_block_fields', array( $this, 'add_fields' ) );
	}

	/**
	 * @param array<array<string,mixed>> $fields
	 * @return array<array<string,mixed>>
	 */
	public function add_fields( array $fields ): array {
		$wp_roles     = wp_roles();
		$role_options = array();
		foreach ( Init::ROLE_OPTIONS as $role_option ) {
			$role_options[ $role_option ] = $wp_roles->get_names()[ $role_option ] ?? $role_option;
		}

		$extra_fields = array(
			TrueFalse::make( _x( 'Require user roles for access', 'Fields', 'fdmg-labels-admin' ), self::META_REQUIRE_ROLES_FOR_ACCESS )
				->stylized()
				->default( true ),
			Select::make( _x( 'Allowed user roles', 'Fields', 'fdmg-labels-admin' ), self::META_ALLOWED_USER_ROLES )
				->choices( $role_options )
				->default( array( Capabilities::ROLE_FDMG_SUBSCRIBER ) )
				->multiple()
				->required()
				->conditionalLogic(
					array(
						ConditionalLogic::where( self::META_REQUIRE_ROLES_FOR_ACCESS, '==', '1' ),
					)
				),
		);

		$fields = array_merge(
			$fields, array_map(
				function ( Field $extra_field ): array {
					return $extra_field->get( Key::sanitize( ACFBlock::FIELD_GROUP ) );
				}, $extra_fields
			)
		);

		return $fields;
	}
}
