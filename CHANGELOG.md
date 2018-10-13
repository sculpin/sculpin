# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## 2.1.2 - 2018-10-12

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#393](https://github.com/sculpin/sculpin/issues/393) Non-existent flag being
  passed to YamlConfigurationBuilder due to an accidental BC break in the
  dflydev/dot-access-configuration library.
- [#343](https://github.com/sculpin/sculpin/pull/343) slugs are used literally
  and not escaped again.

## 2.1.1 - 2017-03-24

### Fixed

- [#345](https://github.com/sculpin/sculpin/pull/345) fixed regression in permalink
 generator introduced by [#233](https://github.com/sculpin/sculpin/pull/233)
- [#281](https://github.com/sculpin/sculpin/pull/281) fixed pagination generator
  not producing page for empty list of items.
