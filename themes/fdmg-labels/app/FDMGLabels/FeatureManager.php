<?php
namespace FDMGLabels;

use Clarkson\Theme\ACFPages\Features;

class FeatureManager {
	public const FEATURE_COMPACT_HEADER = 'compact_header';

	public function register_hooks(): void {
		add_filter( 'clarkson_context_args', array( $this, 'add_context_args' ) );
	}

	/**
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		$context['feature_manager'] = $this;
		return $context;
	}

	/**
	 * Check if feature is enabled via theme options.
	 *
	 * We don't use the get_field function here, because it's only available after the acf/init hook,
	 * while this functionality somtimes needs to be called before that hook.
	 *
	 * @param FeatureManager::FEATURE_* $feature The feature name
	 */
	public function is_enabled( string $feature ): bool {
		return (bool) get_option( sprintf( 'options_%s_%s', Features::META_FEATURES, $feature ), false );
	}
}
