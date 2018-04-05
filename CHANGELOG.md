# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 - TBD

### Added

- New `--output-dir` flag allows users to override the default output
  directory (`output_dev`, `output_prod`) with their own custom target.
  - Also works with `SCULPIN_OUTPUT_DIR` environment variable.

### Changed

- [#341](https://github.com/sculpin/sculpin/pull/341) bumped minimum symfony
  packages version to 3.2.6

### Deprecated

- Nothing.

### Removed

- [#332](https://github.com/sculpin/sculpin/pull/332) dropped php 5 support.
- [#335](https://github.com/sculpin/sculpin/pull/335) removed embedded composer
  and related commands: install, update, self-update and dump-autoload

### Fixed

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
