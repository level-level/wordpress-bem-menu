<?php
namespace FDMGLabels\Paywall;

use Clarkson\WPadmin\Capabilities;

class Init {

	/**
	 * @var array<Capabilities::ROLE_*> Roles that can bypass the paywall.
	 */
	public const ROLE_OPTIONS = array( // @phpstan-ignore-line TEMP, REMOVE THIS LINE WHEN ROLE IS ADDED
		Capabilities::ROLE_FDMG_SUBSCRIBER,
	);

	public function register_hooks(): void {
		( new ACFFields() )->register_hooks();
		( new ContentAccess() )->register_hooks();
		( new ACFBlock() )->register_hooks();

		add_filter( 'll_paywall_paywall_object_classname', array( $this, 'change_paywall_object_class' ) );
	}

	public function change_paywall_object_class( string $classname ): string {
		return PaywallObject::class;
	}
}
