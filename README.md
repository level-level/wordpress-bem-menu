[![GitHub Actions status](https://github.com/level-level/clarkson-theme-dawn/workflows/Build%20%26%20test/badge.svg)](https://github.com/level-level/clarkson-theme-dawn/actions)

# Clarkson Theme Dawn
Clarkson Theme is a modern set of tools to rocket WordPress theme development into the 21st century without losing touch with WordPress platform conventions. It uses [Clarkson Core](https://github.com/level-level/Clarkson-Core) to integrate Twig as the preferred templating engine.

## Starting a new project ✨
Use [the clarkson-theme-dawn repository](https://github.com/level-level/clarkson-theme-dawn) as the [template for your new project](https://docs.github.com/en/github/creating-cloning-and-archiving-repositories/creating-a-repository-from-a-template).

Then follow these steps:

1. Clone the new repository locally.

2. Replace `clarkson-theme-dawn` with the GitHub repository name in the entire project.
**Example:**
`https://github.com/level-level/`**`my-project`** replaces `clarkson-theme-dawn` with **`my-project`**.

3. Rename the folder `clarkson-theme-dawn` in `wp-content/themes` to the same as above (i.e. `my-project`).
**Example**: `https://github.com/level-level/`**`my-project`** renames the  `/themes/clarkson-theme-dawn/` folder to  **`/themes/my-project/`**.

4. Replace `Clarkson Theme Dawn` (case sensitive) throughout the entire project with your project name (i.e. `My Theme`).

5. Run `composer update --lock` from the repo root

6. Set up the Github Actions secrets (see below).

7. In the repository settings on GitHub set the following:

    1. Disable the wiki.
    2. Disable issues.
    3. Disable projects.
    4. Automatically delete head branches after merge.
    5. Protect the master branch, requiring at least 1 approved code review. Disallow direct commits to master.

8. Set up envoyer based on [zzz. Deploy template](https://envoyer.io/projects/63092/). Don't forget to change the theme directory name in all hooks.

9. Set the default language in the Localization section in the readme. 

10. Remove the section *'Starting a new project'* from the readme (it's this section that you are reading right now 🤯).

11. Commit everything and push.

## Local install
Setup a new WordPress install with [VVV](https://varyingvagrantvagrants.org/) (for example my-site)
Remove the existing `wp-content` folder and from the WordPress install root run the following commands:
```
git clone git@github.com:level-level/clarkson-theme-dawn.git wp-content
cd wp-content
composer install -o
```

### Install dependencies and build theme assets
In the theme directory clarkson-theme-dawn run in the command line:

```bash
npm ci
npm run build
````

If you are prompted for passwords: you can find those in the Level Level LastPass.

Run `composer run test` to verify results locally (more info about tests later in this file)

## Localization
Text and slug localization via Loco Translate.

Default text language for this repo is:

- [ ] Dutch
- [ ] English

### Text domain
The default text domain for this project is `clarkson-theme-dawn`.

### Slugs
We use a different textdomain specific for custom post type slugs for example: `clarkson-theme-dawn-slugs`.

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
