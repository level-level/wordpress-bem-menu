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
				'socialShare' => __( 'I read this article via Impact Investor and I would like to share it with you:', 'impact-investor' ),
			)
		);
	}
}
