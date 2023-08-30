<?php
namespace Clarkson\Theme;

use Clarkson\Theme\ACFPages\Features;
use Clarkson\Theme\ACFPages\Paywall;
use Clarkson\Theme\ACFPages\PodcastOptions;
use Clarkson\Theme\ACFPages\SearchOptions;
use Clarkson\Theme\ACFPages\ThemeOptions;
class ACFPages {

	protected const ACF_PAGES = array(
		ThemeOptions::class,
		Features::class,
		Paywall::class,
		PodcastOptions::class,
		SearchOptions::class,
	);

	public function register_hooks(): void {
		foreach ( self::ACF_PAGES as $page ) {
			( new $page() )->register_hooks();
		}
	}
}
