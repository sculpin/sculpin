# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 - TBD

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
