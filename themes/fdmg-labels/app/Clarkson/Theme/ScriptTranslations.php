<?php
namespace Clarkson\Theme;

class ScriptTranslations {
	public function register_hooks(): void {
		wp_localize_script(
			'clarkson_main',
			'i18n',
			array(
				'socialShare'      => sprintf(
					_x( 'I read this article via %s and I would like to share it with you:', 'Share', 'fdmg-labels-admin' ),
					get_bloginfo( 'name' )
				),
				'copied'           => _x( 'Copied', 'Share', 'fdmg-labels' ),
				'invalidDateInput' => _x( 'Invalid date entered', 'Search', 'fdmg-labels' ),
				'formError'        => _x( 'There is a problem with your request. Check the notifications below.', 'Search', 'fdmg-labels' ),
				'loggedIn'         => _x( 'You are logged in as', 'Login', 'fdmg-labels' ),
			)
		);
	}
}
