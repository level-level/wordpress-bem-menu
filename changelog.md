# Changelog
All notable changes for Impact Investor will be documented in this file. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## November 10, 2021
### Changed
- Breakpoints refactor

## November 8, 2021
### Added
- `hover-focus-style` mixin to encapsulate focus and hover styling

## November 4, 2021
### Added
- Adds `render_block` function again after it has been removed by accident

## October 18, 2021
### Fixed
- ACF post_object output, only return Clarkson Object if output value is 'object'
- ACF Relationship field fix notice empty value
- CSS variable content-width

### Added
- Clarkson object available in ACF blocks with `block.get_object()`
- Default block type arg, the category, mode and disable custom class name

### Changed
- Search input disable autocomplete, autocapitalize and spellcheck
- Disables block templates

## October 12, 2021
### Changed
- Use navigation menu name for the nav aria-label

## October 7, 2021
### Changed
- Import blocks.js in to editor.js instead of it's own entry and enqueue

## October 4, 2021
### Changed
- Use custom acf block preview wrapper class `.clarkson-acf-preview-block` for editor styling

## September 29, 2021
### Added
- Default Gravity Forms options including label placement and enabling the validation summary option
- Tab logic to close dropdowns on tab
- Adds ACF Block validation

## September 27, 2021
### Changed
- Add context to translatable strings

## September 22, 2021
### Changed
- Disables all default embeds and enable only youtube, twitter, vimeo, spotify and soundcloud by default
- Remove get_featured_image function and use the get_thumbnail_id function instead
- Adds ACF previews
- Remove block wrapping and use the default block editor alignments

## September 16, 2021
### Added
- theme.json added to disable dropcaps, color settings & custom font sizes by default

## September 7, 2021
### Added
- Shortcodes for Cookiebot, `cookiebot_renew` and `cookiebot_withdraw`

## August 24, 2021
### Added
- `npm run optimize-images` cli command.

## August 4, 2021
### Added
- Storybook checklist item in pull_request_template.md
- .editorconfig rules for acf-json

## July 28, 2021
### Changed
- Fix deprecated notices for WP 5.8
- Refactor layout SASS code
- Use new a11y suggestion for aria-label on logo

## July 12, 2021
### Changed
- Disable cookie notice for vimeo video's with dnt=1 parameter

## July 6, 2021
### Added
- Assets directory path to default emails

## July 1, 2021

### Removed
- Unnecessary Hooks folder

### Fixed
- Wrong ACF meta showing in ACF Gutenberg blocks

## June 24, 2021
### Added
- Default social media icons
- WPseo component
- Mixin for horizontal lists

### Changed
- Refactor social share component

## June 22, 2021

### Added
- Storybook package
- Default responsive styling for the core/table block

## April 22, 2021

### Added
- Default archive layout

## April 21, 2021

### Added
- Default Posts GB block

## April 20, 2021

### Added
- Legal navigation
- Default theme ACF option page
- Default image_size Class file
- Default CTA GB block

### Changed
- Refreshed default colors :D

## April 7, 2021

### Removed
- LL Tools kss Styleguide

## March 30, 2021

### Added
- Dependabot for npm packages: weekly check for npm packages updates

### Changed
- Updated custom webpack config with Level Level webpack-config, see [related docs](https://github.com/level-level/webpack-config).
- Updated custom eslint config with [Level Level eslint-config](https://github.com/level-level/eslint-config-level-level)
- Updated custom stylelint config with [Level Level styelint-config](https://github.com/level-level/stylelint-config-level-level)
- Updated existing JavaScript helpers (tab focus and responsive helper) with [Recyclops](https://github.com/level-level/recyclops) package.

## March 10, 2021

### Added
- Adds support for lazyloading audio embeds such as Spotify and Soundcloud.

## January 13, 2021

### Changed
- Automatically add ACF custom blocks to `allowed_block_types`, so we don't need to manually add new acf blocks.

## December 16, 2020
### Added
- Adds search.twig enhancements:
- Use found_posts and better headings for SEO
- Use uniqid function in the search.twig partial
- Adds default no results message

## December 15, 2020
### Added
- Add width and height property to example logo

### Changed
- Move wp-block-embed and wp-block-image outside of entry-content class
- Use div elements instead of section elements

### Removed
- Unused twig file for the styleguide

## December 14, 2020
### Added
- New JavaScript helper for responsive checks, for example `ResponsiveHelper.isDesktop()`
- Adds GDPR proof oEmbeds

### Changed
- Disabled Gutenberg block patterns
- Extend font-size mixin with line-height calculation
- Use `button-element.twig` for Gravity Forms submit button

### Removed
- Default font smoothing `-webkit-font-smoothing: antialiased;`
- Core embeds facebook and instagram (reason: the new required authentications tokens)

### Fixed
- Added missing `add_new` label for Example custom post type

## November 30, 2020

### Changed
- Updated clarkson-core v0.4.3 to v1.0.0
  - Moved WordPress Object from `themes/impact-investor/wordpress-objects` to `themes/impact-investor/app/WordPress_Objects/`
  - Use new object hierarcy and changed `ACFBlock` to `base_block` and `Base_Object` to `base_object`
  - Use new `clarkson_core_template_context` filter for Assets context args
- Updated clarkson-wp-cli-twig-translations v0.2.0 to v1.0.0
- Updated clarkson-404 v0.1.1 to v1.0.0

### Fixed
- Wrong html closing tag in `index.twig`
