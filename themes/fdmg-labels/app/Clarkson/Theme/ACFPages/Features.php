<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Tab;
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
				'menu_title'  => _x( 'Features', 'Options', 'fdmg-labels-admin' ),
				'page_title'  => _x( 'Features', 'Options', 'fdmg-labels-admin' ),
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
				'title'    => _x( 'Features', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'Features', 'Fields', 'fdmg-labels-admin' ), self::META_FEATURES )
					->instructions( _x( 'Enable or disable features.', 'Fields', 'fdmg-labels-admin' ) )
						->fields(
							array(
								Tab::make( _x( 'Content types', 'Fields', 'fdmg-labels-admin' ) )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Enable Partner post type', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_PARTNER_POST_TYPE )
									->instructions( _x( 'Enables the Partner post type for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable Podcast post type', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_PODCAST_POST_TYPE )
									->instructions( _x( 'Enables the Podcast post type for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable Event post type', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_EVENT_POST_TYPE )
									->instructions( _x( 'Enables the Event post type for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Disable Category taxonomy', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_DISABLE_CATEGORY_TAXONOMY )
									->instructions( _x( 'Disable the Category taxonomy for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable Genre taxonomy', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY )
									->instructions( _x( 'Enables the Genre taxonomy for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable Section taxonomy', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_SECTION_TAXONOMY )
									->instructions( _x( 'Enables the Section taxonomy for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi(),
								Tab::make( _x( 'Layout', 'Fields', 'fdmg-labels-admin' ) )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Enable compact page header', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_COMPACT_HEADER )
									->instructions( _x( 'Enables a more compact header element on top of every page.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Disable article teaser footer', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_DISABLE_ARTICLE_TEASER_FOOTER )
									->instructions( _x( 'Disables the footer of the article teasers.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi(),
								TrueFalse::make( _x( 'Enable "Free article" paywall label', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_PAYWALL_LABEL )
									->instructions( _x( 'If the paywall plugin is active, show the "Free article" label in the article teasers.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi(),
								Tab::make( _x( 'Share options', 'Fields', 'fdmg-labels-admin' ) )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Enable sharing via Facebook', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_FACEBOOK )
									->instructions( _x( 'Enables sharing an article via Facebook.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi()
									->defaultValue( false ),
								TrueFalse::make( _x( 'Enable sharing via X (Twitter)', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_TWITTER )
									->instructions( _x( 'Enables sharing an article via X (Twitter).', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi()
									->defaultValue( true ),
								TrueFalse::make( _x( 'Enable sharing via LinkedIn', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_LINKEDIN )
									->instructions( _x( 'Enables sharing an article via LinkedIn.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi()
									->defaultValue( true ),
								TrueFalse::make( _x( 'Enable sharing via WhatsApp', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_WHATSAPP )
									->instructions( _x( 'Enables sharing an article via WhatsApp.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi()
									->defaultValue( false ),
								TrueFalse::make( _x( 'Enable sharing via E-mail', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_EMAIL )
									->instructions( _x( 'Enables sharing an article via E-mail.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi()
									->defaultValue( true ),
								TrueFalse::make( _x( 'Enable copying to clipboard', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_COPY_CLIPBOARD )
									->instructions( _x( 'Enables sharing by copying the link to the clipboard.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi()
									->defaultValue( false ),
								Tab::make( _x( 'Block level options', 'Fields', 'fdmg-labels-admin' ) )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Disable autofilling the related articles block', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_DISABLE_AUTOFILLING_RELATED_ARTICLES_BLOCK )
									->instructions( _x( 'Make sure the related article block does not automatically load any related articles when creating a new article thus making it invisible.', 'Fields', 'fdmg-labels-admin' ) )
									->stylisedUi()
									->defaultValue( false ),
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
