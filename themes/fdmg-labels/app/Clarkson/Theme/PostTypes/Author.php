<?php
namespace Clarkson\Theme\PostTypes;

use Extended\ACF\Fields\Email;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Url;
use Extended\ACF\Location;

class Author {
	public const POST_TYPE_NAME = 'ii_team';

	public const META_EMAIL    = 'email';
	public const META_LINKEDIN = 'linkedin';
	public const META_LOCATION = 'location';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register_fields(): void {
			register_extended_field_group(
				array(
					'title'    => _x( 'Author', 'Fields', 'fdmg-labels' ),
					'fields'   => array(
						Email::make( _x( 'E-mail address', 'Fields', 'fdmg-labels' ), self::META_EMAIL ),
						Url::make( _x( 'LinkedIn', 'Fields', 'fdmg-labels' ), self::META_LINKEDIN )
						->placeholder( 'https://linkedin.com/' ),
						Text::make( _x( 'Location', 'Fields', 'fdmg-labels' ), self::META_LOCATION ),
					),
					'location' => array(
						Location::where( 'post_type', '==', self::POST_TYPE_NAME ),
					),
					'position' => 'side',
				)
			);
	}
}
