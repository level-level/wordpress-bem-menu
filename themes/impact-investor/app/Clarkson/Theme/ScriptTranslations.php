<?php
namespace Clarkson\Theme;

class ScriptTranslations {
	public function register_hooks(): void {
		wp_localize_script(
			'clarkson_main',
			'i18n',
			array(
				'navigation'  => array(
					'subMenu' => __( 'Sub menu', 'impact-investor' ),
				),
				'socialShare' => __( 'An interesting article', 'impact-investor' ),
			)
		);
	}
}
