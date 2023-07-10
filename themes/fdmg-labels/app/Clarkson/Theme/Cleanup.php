<?php
namespace Clarkson\Theme;

/**
* Rewrite a better title
* @see https://github.com/eddiemachado/bones/blob/master/library/bones.php#L56
*/
class Cleanup {

	public function register_hooks(): void {
		add_filter( 'wp_title', array( $this, 'clarkson_title' ), 10, 2 );
	}

	/**
	 * In some case the WordPress title needs to be cleaned for paging.
	 */
	public function clarkson_title( string $title, string $sep, string $seplocation = 'right' ): string {
		global $page, $paged;

		// Don't affect in feeds.
		if ( is_feed() ) {
			return $title;
		}

		// Add the blog's name.
		if ( 'right' === $seplocation ) {
			$title .= get_bloginfo( 'name' );
		} else {
			$title = get_bloginfo( 'name' ) . $title;
		}

		// Add the blog description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$title .= " {$sep} {$site_description}";
		}

		// Add a page number if necessary:
		if ( $paged >= 2 || $page >= 2 ) {
			$title .= " {$sep} " . sprintf( _x( 'Page %s', 'SEO', 'fdmg-labels' ), max( $paged, $page ) );
		}
		return $title;
	}
}
