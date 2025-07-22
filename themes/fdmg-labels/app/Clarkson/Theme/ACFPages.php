<?php
namespace Clarkson\Theme;

use Clarkson\Theme\ACFPages\FDMGAPI;
use Clarkson\Theme\ACFPages\FDMGContentAPI;
use Clarkson\Theme\ACFPages\Features;
use Clarkson\Theme\ACFPages\Paywall;
use Clarkson\Theme\ACFPages\SearchOptions;
use Clarkson\Theme\ACFPages\SEOOptions;
use Clarkson\Theme\ACFPages\SiteSettings;
use Clarkson\Theme\ACFPages\ThemeOptions;
use Clarkson\Theme\ACFPages\VacancyOptions;

class ACFPages {

	protected const ACF_PAGES = array(
		ThemeOptions::class,
		SiteSettings::class,
		Features::class,
		Paywall::class,
		SearchOptions::class,
		FDMGAPI::class,
		FDMGContentAPI::class,
		SEOOptions::class,
		VacancyOptions::class,
	);

	public function register_hooks(): void {
		foreach ( self::ACF_PAGES as $page ) {
			( new $page() )->register_hooks();
		}
	}
}
