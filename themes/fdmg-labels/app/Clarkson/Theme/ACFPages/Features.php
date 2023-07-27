<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Checkbox;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;

class Features {

	protected const FIELD_GROUP = 'features';

	public const MENU_SLUG     = 'acf-features-options';
	public const META_FEATURES = 'features';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'menu_title'  => _x( 'Features', 'Options', 'fdmg-labels' ),
				'page_title'  => _x( 'Features', 'Options', 'fdmg-labels' ),
				'menu_slug'   => self::MENU_SLUG,
				'parent_slug' => ThemeOptions::MENU_SLUG,
				'icon_url'    => 'dashicons-admin-settings',
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Features', 'Fields', 'fdmg-labels' ),
				'fields'   => array(
					Group::make( _x( 'Features', 'Fields', 'fdmg-labels' ), self::META_FEATURES )
					->instructions( _x( 'Enable or disable features.', 'Fields', 'fdmg-labels' ) )
						->fields(
							array(
								TrueFalse::make( _x( 'Enable compact page header', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_COMPACT_HEADER )
									->instructions( _x( 'Enables a more compact header element on top of every page.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable partner post type', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_ENABLE_PARTNER_POST_TYPE )
									->instructions( _x( 'Enables the partner post type for this site.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable podcast post type', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_ENABLE_PODCAST_POST_TYPE )
									->instructions( _x( 'Enables the podcast post type for this site.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable event post type', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_ENABLE_EVENT_POST_TYPE )
									->instructions( _x( 'Enables the event post type for this site.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable genre taxonomy', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY )
									->instructions( _x( 'Enables the genre taxonomy for this site.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable section taxonomy', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_ENABLE_SECTION_TAXONOMY )
									->instructions( _x( 'Enables the section taxonomy for this site.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable sharing via Facebook', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_SHARE_VIA_FACEBOOK )
									->instructions( _x( 'Enables sharing an article via Facebook.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi()
									->defaultValue( false ),
								TrueFalse::make( _x( 'Enable sharing via Twitter', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_SHARE_VIA_TWITTER )
									->instructions( _x( 'Enables sharing an article via Twitter.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi()
									->defaultValue( true ),
								TrueFalse::make( _x( 'Enable sharing via LinkedIn', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_SHARE_VIA_LINKEDIN )
									->instructions( _x( 'Enables sharing an article via LinkedIn.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi()
									->defaultValue( true ),
								TrueFalse::make( _x( 'Enable sharing via WhatsApp', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_SHARE_VIA_WHATSAPP )
									->instructions( _x( 'Enables sharing an article via WhatsApp.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi()
									->defaultValue( false ),
								TrueFalse::make( _x( 'Enable sharing via E-mail', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_SHARE_VIA_EMAIL )
									->instructions( _x( 'Enables sharing an article via E-mail.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi()
									->defaultValue( true ),
								TrueFalse::make( _x( 'Enable copying to clipboard', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_SHARE_VIA_COPY_CLIPBOARD )
									->instructions( _x( 'Enables sharing by copying the link to the clipboard.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi()
									->defaultValue( false ),
								TrueFalse::make( _x( 'Enable autofill related articles', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_AUTOFILL_RELATED_ARTICLES )
									->instructions( _x( 'Enables autofilling the related articles block', 'Fields', 'fdmg-labels' ) )
									->stylisedUi()
									->defaultValue( true ),
							)
						),
				),
				'location' => array(
					Location::where( 'options_page', self::MENU_SLUG ),
				),
			)
		);
	}
}
