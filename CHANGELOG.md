# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.3.0 - 2024-12-??

### Changed

* [Replace Dflydev\Canal with League\MimeTypeDetection for MIME type detection because Canal is not maintained](https://github.com/sculpin/sculpin/commit/fc822d959ed42be8b581be3ffa444ddea1a28ad3) (thanks, @pronskiy!)
* [Use Twig 3](https://github.com/sculpin/sculpin/commit/535c3cd2696cd88a6cd9d1a051ca17bc9ede3e97) (thanks, @saundefined!)
* [Use Symfony 5.4](https://github.com/sculpin/sculpin/commit/0c83ea7ce51ed0563a80d6ba6525955b5f5361c0) (thanks, @saundefined!)
* [Replace getRootDir with getProjectDir](https://github.com/sculpin/sculpin/commit/f47817e20d7b1ec20d63d8f1e84af1516292cb94) (thanks, @saundefined!)
* [Update dflydev/dot-access-configuration](https://github.com/sculpin/sculpin/commit/b2cf6560d9912ae3bcb26ff619434ca5a4192e64) (thanks, @Pekhov14!)
* [Use EnglishInflector instead Inflector](Use EnglishInflector instead Inflector) (thanks, @saundefined!)

### Removed
* [Remove dependency dflydev/apache-mime-types + add symfony/mime alternative](https://github.com/sculpin/sculpin/commit/8b4b8aa897fff056dbe64e32d057868fb19fb483) (thanks, @Pekhov14!)

### Fixed
* [Fixed syntax deprecations in string variable interpolation](https://github.com/sculpin/sculpin/commit/467ffd4d7d0e3b28c8b8234d7f29bb3c8caf6c44) (thanks, @pronskiy!)
* [PHP 8.4 Updates to avoid implicit nulable parameter deprecation](https://github.com/sculpin/sculpin/commit/17f66eecedcda8ac59f685f960748b1a50dd87c3) (thanks, @Ayesh!)

## 3.2.0 - 2022-10-31

### Added

* [Add exit codes to Command classes](https://github.com/sculpin/sculpin/commit/e62af1e055044a5c294938d2380c9475049628c6)
* [Add Sculpin::EVENT_AFTER_GENERATE](https://github.com/sculpin/sculpin/commit/23a74baef263de9cca8e35627b6e36b9ef12a712) (thanks @sunadarake!)
* [Add .php-version and .phpunit.result.cache to gitignore](https://github.com/sculpin/sculpin/commit/a188f66c80439f1e3a4c14344bad24572644ad71) (thanks @sunadarake!)
* [Add PHP attributes to suppress some PHP 8.1 deprecation notices](https://github.com/sculpin/sculpin/commit/44c4d402060d10018447177ae800ce17dc201609)

### Removed

* [Remove IntlExtension configuration - this removes several localized filters in twig](https://github.com/sculpin/sculpin/commit/3b4db353065a413be5783b5a1491d57ee92d3940)
* [Remove twig/extensions dependency](https://github.com/sculpin/sculpin/commit/80f67a7139b4304c335ccfb00a89d21a1c214801)
* [Remove unnecessary third constructor parameter to ConsoleIo](https://github.com/sculpin/sculpin/commit/5f9bbe471f08029c63a23913726a2c99b171024c)

### Fixed

* [Don't pass null to normalize in SourcePermalinkFactory::generatePermalinkPathname()](https://github.com/sculpin/sculpin/commit/0f67f3a711fc1ee2fa38e4b4cb6b93d0293f57e2)
* [Only overwrite value in SourcePermalinkFactory::normalize if iconv() succeeds](https://github.com/sculpin/sculpin/commit/714081796589dbea065042e9dc302f95a9fded24) (thanks @friartuck6000!)
* [Update composer and allow theme composer plugin config changes](https://github.com/sculpin/sculpin/commit/d04faae18751a2af3494daa9c7791f661a916f98)
* [Override PhpMarkdownExtraParser's doExtraAttributes method to cast $attr to string](https://github.com/sculpin/sculpin/commit/5f705d845b2dc980ed91b79c49ccaa5f64cbdda0)

Also, the primary branch has been renamed to `main`.

## 3.1.1 - 2021-07-15

### Fixed

* [vdelau](https://github.com/sculpin/sculpin/commits?author=vdelau) fixed an issue in the bin/sculpin script to improve windows compatibility (Thanks!) [#463](https://github.com/sculpin/sculpin/pull/463)
* [sunadarake](https://github.com/sunadarake) fixed a deprecated method in TreeBuilder (Thanks!) [#460](https://github.com/sculpin/sculpin/pull/460) 

## 3.1.0 - 2020-12-01

### Added

* adds webpack encore helper class [#446](https://github.com/sculpin/sculpin/pull/446)

### Changed

* adds support for PHP 8.0, and drops support for PHP 7.2
* [clue](https://github.com/sculpin/sculpin/commits?author=clue) updated React/HTTP to v1.0.0 (Thanks!)

## 3.0.3 - 2019-12-04

### Fixed

* PR #438 upgrades the michelf/php-markdown library to version 1.9,
  fixing some deprecated syntax notices under PHP 7.4

## 3.0.2 - 2019-05-26

### Fixed

* Issues #432 and #434 have been fixed. When processing content types,
  such as entries in `source/_posts/*`, Sculpin will now skip certain
  files.
  * "hidden" files (beginning with a `.`) will be skipped silently.
    * This helps suppress files like `.DS_Store`.
  * files which aren't recognized by any registered Formatter will be
    skipped with a "Skipped ..." message.
    * This helps combat strange behaviour for files with no extension.

## 3.0.1 - 2019-04-17

### Added

* Thanks to Rami Jumaah and Christian Riesen, Pages can now refer to
  their own relative pathname and filename using `page.relative_pathname`
  and `page.filename`. This can be useful for implementing an "Edit This
  Page" link directly to a GitLab or GitHub VCS URL.

### Changed

* Boilerplate is now generated by default when using `content:create`
  * Use `--dry-run`/`-d` to suppress the creation of these files
  * Note that the YAML content type definition still needs to be added
    by hand to your `app/config/sculpin_kernel.yml` file
  * Taxonomy-related boiler plate files now default to using the default
    layout instead of no layout at all. This should result in prettier
    output.

### Deprecated
### Removed
### Fixed

* ProxySourceCollection now uses the StableSort algorithm from Martijn
  van der Lee to ensure stable sorting results, which will eliminate
  some of the issues reported in bug #308 that were causing `generate`
  to constantly repeat in watch mode.

## 3.0.0 - 2019-04-09

### Added

- New `--output-dir` flag allows users to override the default output
  directory (`output_dev`, `output_prod`) with their own custom target.
  - Also works with `SCULPIN_OUTPUT_DIR` environment variable.
- New `--source-dir` flag allows users to override the default source
  directory (`source`) with their own custom target.
  - Also works with `SCULPIN_SOURCE_DIR` environment variable.
- New `content:create` command allows users to quickly and easily create
  new content types. It will generate appropriate templates, including
  pagination.
- New `init` command allows users to spin up an **EXTREMELY** bare-bones
  Sculpin site, automatically creating YAML configuration files and some
  placeholder `source/` files.
  - Note that "posts" are disabled in this bare-bones configuration. If
    you would like to create a traditional Blog site, definitely use an
    existing skeleton project such as:
    https://github.com/sculpin/sculpin-blog-skeleton

### Changed

- BC BREAK: Many classes have been made final to reduce complexity. If there
  is a valid use case to extend one of the classes, please do a pull request
  to remove the final keyword and explain why you need to extend the class.
- BC BREAK: [#367](https://github.com/sculpin/sculpin/pull/367) moved to
  PHP 7.2 as the new minimum PHP version for Sculpin.
- BC BREAK: [#392](https://github.com/sculpin/sculpin/pull/392) changed
  the signature of the `getAdditionalSculpinBundles()` method to specify
  an `array` return type. If you have a custom `SculpinKernel.php` file
  you will need to
- [#385](https://github.com/sculpin/sculpin/pull/385) bumped minimum
  Symfony packages version to 4.1
  - See the "Fixed" section for a BC Break related to the Symfony YAML
    component.

### Deprecated

- Nothing.

### Removed

- [#332](https://github.com/sculpin/sculpin/pull/332) dropped php 5 support.
- [#335](https://github.com/sculpin/sculpin/pull/335) removed embedded composer
  and related commands: install, update, self-update and dump-autoload

### Fixed

- BC BREAK: Pagination file names can now use the ".twig" extension, and
  when using this mode, "Page 2" will render as "blog/page/2/index.html"
  instead of "blog/page/2.html"
  - To preserve search engine indexes and/or bookmarks, consider
    creating redirect mappings to send visitors to the new destinations.
- BC BREAK: Upgrading the Symfony YAML component has resulted in some
  changes to YAML Front Matter processing.
  - Most notably, values with a **colon**, such as page titles, now
    require quoting. E.g., `title: My Journey: Back From Whence I Came`
    must now be `title: "My Journey: Back From Whence I Came"`
  - These instances should be clearly called out the first time you
    call the generate command, so it should require minimal effort to
    locate and fix.

## 2.1.2 - 2018-10-12

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#343](https://github.com/sculpin/sculpin/pull/343) slugs are used literally
  and not escaped again.

## 2.1.1 - 2017-03-24

### Fixed

- [#345](https://github.com/sculpin/sculpin/pull/345) fixed regression in permalink
 generator introduced by [#233](https://github.com/sculpin/sculpin/pull/233)
- [#281](https://github.com/sculpin/sculpin/pull/281) fixed pagination generator
  not producing page for empty list of items.
