<?php

namespace FDMGLabels\ServerSideTracking;

use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\WordPress_Object\user;
use FDMGLabels\Paywall\PaywallObject;
use LevelLevel\Paywall\Init as PaywallInit;
use WP_Post;

class Init {
	public function register_hooks(): void {
		add_filter( 'fdmg_server_side_tracking_page_variables', array( $this, 'track_page_data' ) );
		add_filter( 'fdmg_server_side_tracking_user_variables', array( $this, 'track_user_data' ), 10, 2 );
	}

	/**
	 * Adds page data to the server side tracking variables.
	 *
	 * @param array<string,string> $variables
	 * @return array<string,string>
	 */
	public function track_page_data( array $variables ): array {
		$variables['paywall'] = 'free';
		if (
			is_plugin_active( 'll-paywall/ll-paywall.php' )
			&& is_singular(
				( new PaywallInit() )->get_supported_post_types()
			)
		) {
			$post          = get_queried_object();
			$paywall_block = $post instanceof WP_Post ? PaywallObject::get_from_post( $post ) : null;
			if ( $paywall_block instanceof PaywallObject ) {
				$variables['paywall'] = $paywall_block->get_require_roles_for_access() ? 'subscription' : 'account';
			}
		}

		return $variables;
	}

	/**
	 * Adds user data to the server side tracking variables.
	 *
	 * @param array<string,string> $variables
	 * @return array<string,string>
	 */
	public function track_user_data( array $variables, int $user_id ): array {
		$custom_user_data = array(
			'fdmg_user_id'         => '0',
			'fdmg_organisation_id' => '0',
			'has_subscription'     => '0',
		);

		$user = $user_id ? user::get( $user_id ) : null;
		if ( ! $user instanceof user ) {
			return array_merge( $variables, $custom_user_data );
		}

		$custom_user_data = array(
			'fdmg_user_id'         => (string) $user->get_fdmg_user_id(),
			'fdmg_organisation_id' => (string) $user->get_fdmg_organisation_id(),
			'has_subscription'     => in_array( Capabilities::ROLE_FDMG_SUBSCRIBER, $user->get_roles(), true ) ? '1' : '0',
		);

		return array_merge( $variables, $custom_user_data );
	}
}
