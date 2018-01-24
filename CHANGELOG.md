# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 - TBD

### Added

- Nothing.

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

- Nothing.

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
