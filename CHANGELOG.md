# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.5.1 - 2020-01-07

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#19](https://github.com/laminas-api-tools/api-tools-oauth2/pull/19) adds a missing `<p>` opening tag to the receive-code template.

- Renames the `view/zf/` directory to `view/laminas/`, which is where the code was expecting it following migration.

## 1.5.0 - 2018-05-07

### Added

- [zfcampus/zf-oauth2#167](https://github.com/zfcampus/zf-oauth2/pull/167) adds support for PHP 7.1 and 7.2.

### Changed

- [zfcampus/zf-oauth2#160](https://github.com/zfcampus/zf-oauth2/pull/160) alters `AuthController::tokenAction()` such that it uses the exception code from
  a caught `ProblemExceptionInterface` instance as the ApiProblem status if it falls in the 400-600 range.

- [zfcampus/zf-oauth2#151](https://github.com/zfcampus/zf-oauth2/pull/151) updates `Laminas\ApiTools\OAuth2\Provider\UserId\AuthenticationService` to allow injecting any
  `Laminas\Authentication\AuthenticationServiceInterface` implementation, not just `Laminas\Authentication\AuthenticationService`.

### Deprecated

- Nothing.

### Removed

- [zfcampus/zf-oauth2#167](https://github.com/zfcampus/zf-oauth2/pull/167) removes support for HHVM.

### Fixed

- Nothing.

## 1.4.0 - 2016-07-10

### Added

- [zfcampus/zf-oauth2#149](https://github.com/zfcampus/zf-oauth2/pull/149) adds support for usage
  of ext/mongodb with `Laminas\ApiTools\OAuth2\Adapter\MongoAdapter`; users will need to also
  install a compatibility package to do so:
  `composer require alcaeus/mongo-php-adapter`
- [zfcampus/zf-oauth2#141](https://github.com/zfcampus/zf-oauth2/pull/141) and
  [zfcampus/zf-oauth2#148](https://github.com/zfcampus/zf-oauth2/pull/148) update the component to
  allow usage with v3 releases of Laminas components on which it depends,
  while maintaining backwards compatibility with v2 components.
- [zfcampus/zf-oauth2#141](https://github.com/zfcampus/zf-oauth2/pull/141) and
  [zfcampus/zf-oauth2#148](https://github.com/zfcampus/zf-oauth2/pull/148) add support for PHP 7.
- [zfcampus/zf-oauth2#122](https://github.com/zfcampus/zf-oauth2/pull/122) adds support for token
  revocation via the `/oauth/revoke` path. The path expects a POST request as
  either urlencoded or JSON values with the parameters:
  - `token`, the access token to revoke
  - `token_type_hint => access_token` to indicate an access token is being
    revoked.
- [zfcampus/zf-oauth2#146](https://github.com/zfcampus/zf-oauth2/pull/146) updates the
  `AuthController` to catch `Laminas\ApiTools\ApiProblem\Exception\ProblemExceptionInterface`
  instances thrown by the OAuth2 server and return `ApiProblemResponse`s.

### Deprecated

- Nothing.

### Removed

- [zfcampus/zf-oauth2#141](https://github.com/zfcampus/zf-oauth2/pull/141) removes support for PHP 5.5.

### Fixed

- Nothing.

## 1.3.3 - 2016-07-07

### Added

- Nothing.

### Changed

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
