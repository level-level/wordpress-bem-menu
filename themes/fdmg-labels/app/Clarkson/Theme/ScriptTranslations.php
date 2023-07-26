<?php
namespace Clarkson\Theme;

class ScriptTranslations {
	public function register_hooks(): void {
		wp_localize_script(
			'clarkson_main',
			'i18n',
			array(
				'socialShare' => sprintf(
					_x( 'I read this article via %s and I would like to share it with you:', 'Share', 'fdmg-labels' ),
					get_bloginfo( 'name' )
				),
			)
		);
	}
}
