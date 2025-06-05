<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\DatePicker;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Fields\WYSIWYGEditor;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;

class Features {

	protected const FIELD_GROUP = 'features';

	public const MENU_SLUG     = 'acf-features-options';
	public const META_FEATURES = 'features';

	public const META_MESSAGE_OLD_ARTICLES = 'message_old_articles';
	public const META_DATE                 = 'message';
	public const META_MESSAGE              = 'date';

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
				'parent_slug' => SiteSettings::MENU_SLUG,
				'icon_url'    => 'dashicons-admin-settings',
				'capability'  => 'manage_options',
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
					->helperText( _x( 'Enable or disable features.', 'Fields', 'fdmg-labels-admin' ) )
						->fields(
							array(
								Tab::make( _x( 'Content types', 'Fields', 'fdmg-labels-admin' ) )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Enable Partner post type', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_PARTNER_POST_TYPE )
									->helperText( _x( 'Enables the Partner post type for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized(),
								TrueFalse::make( _x( 'Enable Event post type', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_EVENT_POST_TYPE )
									->helperText( _x( 'Enables the Event post type for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized(),
								TrueFalse::make( _x( 'Disable Category taxonomy', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_DISABLE_CATEGORY_TAXONOMY )
									->helperText( _x( 'Disable the Category taxonomy for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized(),
								TrueFalse::make( _x( 'Enable Genre taxonomy', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY )
									->helperText( _x( 'Enables the Genre taxonomy for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized(),
								TrueFalse::make( _x( 'Enable Section taxonomy', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_SECTION_TAXONOMY )
									->helperText( _x( 'Enables the Section taxonomy for this site.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized(),
								Tab::make( _x( 'Layout', 'Fields', 'fdmg-labels-admin' ) )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Enable compact page header', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_COMPACT_HEADER )
									->helperText( _x( 'Enables a more compact header element on top of every page.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized(),
								TrueFalse::make( _x( 'Disable article teaser footer', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_DISABLE_ARTICLE_TEASER_FOOTER )
									->helperText( _x( 'Disables the footer of the article teasers.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized(),
								TrueFalse::make( _x( 'Enable "Free article" paywall label', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_PAYWALL_LABEL )
									->helperText( _x( 'If the paywall plugin is active, show the "Free article" label in the article teasers if the article does not include the paywall block.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized(),
								Tab::make( _x( 'Share options', 'Fields', 'fdmg-labels-admin' ) )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Enable sharing via Facebook', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_FACEBOOK )
									->helperText( _x( 'Enables sharing an article via Facebook.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( false ),
								TrueFalse::make( _x( 'Enable sharing via X (Twitter)', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_TWITTER )
									->helperText( _x( 'Enables sharing an article via X (Twitter).', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( true ),
								TrueFalse::make( _x( 'Enable sharing via LinkedIn', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_LINKEDIN )
									->helperText( _x( 'Enables sharing an article via LinkedIn.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( true ),
								TrueFalse::make( _x( 'Enable sharing via WhatsApp', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_WHATSAPP )
									->helperText( _x( 'Enables sharing an article via WhatsApp.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( false ),
								TrueFalse::make( _x( 'Enable sharing via E-mail', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_EMAIL )
									->helperText( _x( 'Enables sharing an article via E-mail.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( true ),
								TrueFalse::make( _x( 'Enable copying to clipboard', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_SHARE_VIA_COPY_CLIPBOARD )
									->helperText( _x( 'Enables sharing by copying the link to the clipboard.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( false ),
								Tab::make( _x( 'Block level options', 'Fields', 'fdmg-labels-admin' ) )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Disable autofilling the related articles block', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_DISABLE_AUTOFILLING_RELATED_ARTICLES_BLOCK )
									->helperText( _x( 'Make sure the related article block does not automatically load any related articles when creating a new article thus making it invisible.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( false ),
								TrueFalse::make( _x( 'Disable using a default title in the highlighted content block', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_DISABLE_DEFAULT_TITLE_HIGHLIGHTED_CONTENT_BLOCK )
									->helperText( _x( 'Filling the title field is then no longer required in the highlighted content block.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( false ),

								TrueFalse::make( _x( 'Disable auto preview for post url embeds', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_DISABLE_EMBED_POST_URL_AUTO_PREVIEW )
									->helperText( _x( 'This is only visible in the frontend of the website.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( false ),

								Tab::make( _x( 'Article options', 'Fields', 'fdmg-labels-admin' ) )
									->placement( 'left' ),

								TrueFalse::make( _x( 'Show message in content for old articles', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_MESSAGE_OLD_ARTICLES )
									->helperText( _x( 'Adds a custom message to articles older then x date.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( false ),

								Group::make( _x( 'Message', 'Fields', 'fdmg-labels-admin' ), self::META_MESSAGE_OLD_ARTICLES )
									->fields(
										array(
											WYSIWYGEditor::make( _x( 'Message', 'Fields', 'fdmg-labels-admin' ), self::META_MESSAGE )
												->required()
												->tabs( 'visual' )
												->toolbar( 'basic' )
												->disableMediaUpload(),

											DatePicker::make( _x( 'Date', 'Fields', 'fdmg-labels-admin' ), self::META_DATE )
												->helperText( _x( 'Articles older then this date will show the message above.', 'Fields', 'fdmg-labels-admin' ) )
												->displayFormat( 'd/m/Y' )
												->required(),
										)
									)
									->conditionalLogic(
										array(
											ConditionalLogic::where( FeatureManager::FEATURE_ENABLE_MESSAGE_OLD_ARTICLES, '==', '1' ),
										)
									),

								Tab::make( _x( 'Footer options', 'Fields', 'fdmg-labels-admin' ), 'footer_options' )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Enable B Corp logo', 'Fields', 'fdmg-labels-admin' ), FeatureManager::FEATURE_ENABLE_B_CORP_LOGO )
									->stylized(),

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
