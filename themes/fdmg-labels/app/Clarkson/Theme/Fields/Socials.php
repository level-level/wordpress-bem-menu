<?php
namespace Clarkson\Theme\Fields;

use Extended\ACF\Fields\Select;

class Socials {

	public static function get_list( string $name ): Select {
		return Select::make( _x( 'Type', 'Fields', 'fdmg-labels-admin' ), $name )
			->choices(
				array(
					'facebook'  => 'Facebook',
					'x'         => 'X',
					'instagram' => 'Instagram',
					'linkedin'  => 'Linkedin',
					'youtube'   => 'YouTube',
					'bluesky'   => 'Bluesky',
				)
			)
			->wrapper(
				array(
					'width' => '35',
				)
			)
			->format( 'array' )
			->required()
			->stylized()
			->lazyLoad();
	}
}
