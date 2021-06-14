# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.7.2 - 2021-06-14


-----

### Release Notes for [1.7.2](https://github.com/laminas-api-tools/api-tools-oauth2/milestone/7)

1.7.x bugfix release (patch)

### 1.7.2

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **2**

#### BC Break,Bug

 - [37: Fix BC break in BcryptTrait](https://github.com/laminas-api-tools/api-tools-oauth2/pull/37) thanks to @weierophinney and @cvigorsICBF

## 1.7.1 - 2021-06-11


-----

### Release Notes for [1.7.1](https://github.com/laminas-api-tools/api-tools-oauth2/milestone/5)

### Fixed

- Adds a missing package requirement, webmozar/assert.

### 1.7.1

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **2**

#### Bug

 - [34: Ensure webmozart/assert is present as a package requirement](https://github.com/laminas-api-tools/api-tools-oauth2/pull/34) thanks to @weierophinney and @diego-sorribas

## 1.7.0 - 2021-06-09


-----

### Release Notes for [1.7.0](https://github.com/laminas-api-tools/api-tools-oauth2/milestone/3)

### Added

- This release adds support for PHP 8.0.

### Removed

- This release removes support for PHP versions prior to 7.3.

### 1.7.0

- Total issues resolved: **0**
- Total pull requests resolved: **3**
- Total contributors: **3**

#### Enhancement

 - [32: Switch to GHA CI Workflow](https://github.com/laminas-api-tools/api-tools-oauth2/pull/32) thanks to @weierophinney
 - [29: PHP 8.0 support](https://github.com/laminas-api-tools/api-tools-oauth2/pull/29) thanks to @eimkua1

#### Enhancement,hacktoberfest-accepted

 - [26: Qa/psalm](https://github.com/laminas-api-tools/api-tools-oauth2/pull/26) thanks to @superrosko

## 1.6.0 - 2020-09-10

### Removed

- [#21](https://github.com/laminas-api-tools/api-tools-oauth2/pull/21) removes the `IbmDb2Adapter` and related factory. The functionality was dependent on [an unmerged patch against the upstream bshaffer/oauth2-server-php library](https://github.com/bshaffer/oauth2-server-php/pull/565), and has never worked as a result.


-----

### Release Notes for [1.6.0](https://github.com/laminas-api-tools/api-tools-oauth2/milestone/1)



### 1.6.0

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Enhancement

 - [21: Remove IbmDb2 Adapter](https://github.com/laminas-api-tools/api-tools-oauth2/pull/21) thanks to @alexdenvir

## 1.5.2 - 2020-03-28

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed `replace` version constraint in composer.json so repository can be used as replacement of `zfcampus/zf-oauth2:^1.5.0`.

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
