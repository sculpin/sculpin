# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 - TBD

### Added

- New `content:create` command allows users to quickly and easily create
  new content types. It will generate appropriate templates, including
  pagination.

### Changed

- BC BREAK: [#367](https://github.com/sculpin/sculpin/pull/367) moved to PHP 7.2
  and started Sculpin 3 development
- [#341](https://github.com/sculpin/sculpin/pull/341) bumped minimum symfony
  packages version to 3.2.6

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

## 2.1.2 - TBD

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
