# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.3.3 - 2016-07-07

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-oauth2#147](https://github.com/zfcampus/zf-oauth2/pull/147) fixes an issue in the
  `AuthControllerFactory` introduced originally by a change in laminas-mvc (and
  since corrected in that component). The patch to `AuthControllerFactory` makes
  it forwards compatible with laminas-servicemanager v3, and prevents the original
  issue from recurring in the future.
- [zfcampus/zf-oauth2#144](https://github.com/zfcampus/zf-oauth2/pull/144) removes an unused
  variable from the `receive-code` template.

## 1.3.2 - 2016-06-24

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-oauth2#120](https://github.com/zfcampus/zf-oauth2/pull/120) fixes a typo in the
  `Laminas\ApiTools\OAuth2\Provider\UserId\AuthenticationService` which prevented returning of
  the user identifier.
