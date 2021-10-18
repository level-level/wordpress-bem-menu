<?php
namespace Clarkson\Theme;

class ScriptTranslations {
	public function register_hooks(): void {
		wp_localize_script(
			'clarkson_main',
			'i18n',
			array(
				'navigation'  => array(
					'subMenu' => __( 'Sub menu', 'clarkson-theme-dawn' ),
				),
				'socialShare' => __( 'An interesting article', 'clarkson-theme-dawn' ),
			)
		);
	}
}
