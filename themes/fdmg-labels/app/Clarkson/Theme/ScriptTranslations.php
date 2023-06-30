<?php
namespace Clarkson\Theme;

class ScriptTranslations {
	public function register_hooks(): void {
		wp_localize_script(
			'clarkson_main',
			'i18n',
			array(
				'navigation'  => array(
					'subMenu' => __( 'Sub menu', 'fdmg-labels' ),
				),
				'socialShare' => sprintf(
					__( 'I read this article via %s and I would like to share it with you:', 'fdmg-labels' ),
					get_bloginfo( 'name' )
				),
			)
		);
	}
}
