# Theme

## Webpack

This theme uses a seperate package to handle all webpack things. [This package can be found here](https://github.com/level-level/webpack-config).

To get started duplicate the `config.local.json.example` file and rename it to `config.local.json`. Insert the correct data in to this file.

### Usage

- `npm run start`
- `npm run lint`
- `npm run fix`
- `npm run build`
  - `npm run optimize-images`
- `npm run storybook`
- `npm run storybook:build`

If you run into any problems locally with the initial installation of the node_modules (especially webpack) try setting your local environment to DEV by executing the following on your CLI:

- `export NODE_ENV=development`

The cause of the problem is probably the Node version and the default state it's in (it used to install all packages, but recently changed to production modules only).

### Linters

- [StyleLint package](https://github.com/level-level/stylelint-config-level-level)
- [ESLint package](https://github.com/level-level/eslint-config-level-level)

### Images

Webpack automatically processes images used in the SCSS. For the images that you use directly in twig, for example a logo in the `header.twig`, import them into `index.js`.

```javascript
import './images/logo.jpg';
```

Get the image URL in the .twig files by using the `{{ assets.get_assets_directory_url() }}` function.
The `{{ assets.get_assets_directory_url() }}` function is the replacement of `{{ get_stylesheet_directory_uri() }}`.
For example placing a logo in the header:

```html
<img src="{{ assets.get_assets_directory_url() }}/images/logo.jpg" loading="lazy" alt="" width="200" height="100" />
```

#### Lazyload

We use default browser lazyloading for images.
Usage example:

```html
<img
	src="{{ assets.get_assets_directory_url() }}/images/thumbnail.jpg"
	loading="lazy"
	alt=""
	width="150"
	height="150"
/>
```

### Inline SVG Icons

All svg icons need to be located inside `src/images/icons/`. Make sure to run `npm run optimize-images` to remove unused ids and other attributes.

Usage example:

```html
<span class="icon-inline" aria-hidden="true">{{ svg_icons.get('arrow-right')|raw }}</span>
```

## Storybook

### Setup

Add `define( 'WP_ENVIRONMENT_TYPE', 'local' );` to `wp-config.php`.
Important note: For security reasons Storybook will only work on your local machine and on a staging server.

### Examples

FDMG Labels comes with some [predefined stories](https://github.com/level-level/fdmg-labels/tree/main/themes/fdmg-labels/src/stories).
What is important for a story to work is that a story should always be located in the `src/stories` folder and that the name should include the `.stories` affix.

## Cookiebot

When [Cookiebot](https://www.cookiebot.com) is used on your website, there are shortcodes available to display a button that allows the user to change their cookie preferences.

### Usage

#### Renew shortcode

```txt
[cookiebot_renew]
[cookiebot_renew]Custom button text[/cookiebot_renew]
```

#### Withdraw shortcode

```txt
[cookiebot_withdraw]
[cookiebot_withdraw]Custom button text[/cookiebot_withdraw]
```

### Cookiebot loaded via plugin

If you've installed the [Cookiebot WordPress plugin](https://nl.wordpress.org/plugins/cookiebot/), you're almost done!
Enter your Cookiebot ID in the settings page, and you're good to go.

### Cookiebot loaded via script

If you've loaded Cookiebot using a custom script, you'll need to add the following hook to your theme.

```php
add_filter( 'clarkson_cookiebot_load_shortcodes', '__return_true' );
```
