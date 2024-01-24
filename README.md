[![GitHub Actions status](https://github.com/level-level/fdmg-labels/workflows/Build%20%26%20test/badge.svg)](https://github.com/level-level/fdmg-labels/actions)

# FDMG Labels
Clarkson Theme is a modern set of tools to rocket WordPress theme development into the 21st century without losing touch with WordPress platform conventions. It uses [Clarkson Core](https://github.com/level-level/Clarkson-Core) to integrate Twig as the preferred templating engine.

> :exclamation: **This project has been renamed from "Impact Investor" to "FDMG Labels"**.
>
> You might find some prefixes still of `ii_` or `II_`.
> These exist for backward compatibility reasons, as otherwise a complete migration is needed.
> Please only use a new prefix (like `fdmgl`) for new post types, taxonomies, fields, settings, etc.

## Local install
Setup a new WordPress install with [VVV](https://varyingvagrantvagrants.org/) (for example my-site)
Remove the existing `wp-content` folder and from the WordPress install root run the following commands:
```
git clone git@github.com:level-level/fdmg-labels.git wp-content
cd wp-content
composer install -o
```

### Install dependencies and build theme assets
In the theme directory fdmg-labels run in the command line:

```bash
npm ci
npm run build
```

If you are prompted for passwords: you can find those in the Level Level LastPass.

Run `composer run test` to verify results locally (more info about tests later in this file)

## Localization
Text and slug localization via Loco Translate.

Default text language for this repo is:

- [ ] Dutch
- [x] English

### Text domain
The default text domain for this project is `fdmg-labels`.

### Slugs
We use a different textdomain specific for custom post type slugs for example: `fdmg-labels-slugs`.

### Javascript translations
Add new Javascript string translations via `ScriptTranslations.php`, all string will be available with the `i18n` javascript variable.

## Automated testing
There are multiple code tests in place.
- Composer, to check if the composer files are intact
- PHPCS, to check if you are following the Level Level PHP code standards
- TWIGCS, to check if you are following the Twig code standards
- Psalm tests, that searches for possible mistakes in your code

### GitHub actions
On every pull-request, a GitHub action is run that verifies the project, based on the
`composer run test` results.

### Manually
Run `composer run test` to verify results locally.

#### Test suites
This example is specifically written for `Local`, but most steps should be applicable to other development platforms. This is quite a bit faster, but might not be 100% accurate.

1. Copy `codeception.dist.yml` to `codeception.yml`.
2. Edit the `params` property to point to `.env.testing.local`.
3. Create a new (empty) database.
4. Import `tests/_data/dump.sql` into this new empty database.
5. Copy `.env.testing` to `.env.testing.local`.
6. Edit `.env.testing.local` with your local values.
  - Set the database to the test database everywhere.
  - Set `WP_ROOT_FOLDER` to your local WordPress folder.
  - Set `TEST_SITE_WP_*` to your local values.

After this setup run

`composer test:codeception`

to run the tests in your local setup.

### Fixing

Run `composer run fix` to use automated fixing tools such as phpcbf.

## Working locally with https

If you locally work with https you need to enable Chrome to allow invalid certificates. To enable this go to: `chrome://flags/#allow-insecure-localhost` and enable the `Allow invalid certificates for resources loaded from localhost.` option.

## Editor config
Theme comes with a .editorconfig file. For this to work you need to install a plugin that uses the `.editorconfig` file.
- [Visual Code Studio](https://marketplace.visualstudio.com/items?itemName=EditorConfig.EditorConfig)
- [Atom](https://atom.io/packages/editorconfig)

## ElasticSearch and ElasticPress
This project uses ElasticSearch and ElasticPress for searching and showing related content. For more information on how to configure/setup ElasticPress, please visit our [ElasticPress readme](./themes/fdmg-labels/docs/elasticpress.md).
