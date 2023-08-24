<?php
namespace FDMGLabels\Paywall;

use LevelLevel\Paywall\ObjectPaywall;

class Init {

	/**
	 * @var array<Capabilities::ROLE_*> Roles that can bypass the paywall.
	 */
	public const ROLE_OPTIONS = array( // @phpstan-ignore-line TEMP, REMOVE THIS LINE WHEN ROLE IS ADDED
		'example_role',
	);

	public function register_hooks(): void {
		( new ACFFields() )->register_hooks();
		( new ContentAccess() )->register_hooks();

		add_filter( 'll_paywall_paywall_object_classname', array( $this, 'change_paywall_object_class' ) );
		add_filter( 'll_paywall_template_html', array( $this, 'render_paywall_component' ), 10, 3 );
	}

	public function change_paywall_object_class( string $classname ): string {
		return PaywallObject::class;
	}

	/**
	 * @param string|null $shortcircuit_template
	 * @param string $template
	 * @param array<string,mixed> $template_args
	 */
	public function render_paywall_component( $shortcircuit_template, $template, $template_args ): ?string {
		if ( $template === 'paywall-component' ) {

			$post           = get_post( (int) ( $template_args['post_id'] ?? null ) );
			$paywall_object = $post ? ObjectPaywall::get_from_post( $post ) : null;

			return \Clarkson_Core\Templates::get_instance()->render_twig(
				'partials/components/paywall.twig',
				array_merge(
					$template_args, array(
						'paywall' => $paywall_object,
					),
				),
				true
			);
		}
		return $shortcircuit_template;
	}
}
