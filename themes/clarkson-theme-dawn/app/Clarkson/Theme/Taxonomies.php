<?php
namespace Clarkson\Theme;

use Clarkson\Theme\Taxonomies\ExampleTax;

class Taxonomies {

	protected const TAXONOMIES = array(
		ExampleTax::class,
	);

	public function register_hooks(): void {
		foreach ( self::TAXONOMIES as $taxonomy_class ) {
			( new $taxonomy_class() )->register_hooks();
		}
	}
}
