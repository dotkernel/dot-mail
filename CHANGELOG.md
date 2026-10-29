# Changelog

## 6.0.0 - Unreleased

### Changed

- **BC:** mail services are configured under `dot_mail.services`; `dot_mail.log` stays separate. A configuration in the old layout (`dot_mail.default`) now fails with an `InvalidArgumentException`. Move the service blocks as shown in `config/mail.global.php.dist`.
- **BC:** `send()` restores the configured defaults after every attempt, successful or not: recipients, subject, body and attachments must be set again for every message. `getMessage()` returns a new object after each send.
- **BC:** a missing or unreadable attachment now throws a `MailException` instead of being skipped.
- **BC:** the attachment directory (`attachments.dir`) is scanned at send time instead of once at start-up, and files starting with a dot are skipped. Attachments are read with `DataPart::fromPath()`, so no file handles are left open.
- **BC:** `send()` handles any `Throwable`, and the `MailException` it throws carries the original exception and code. `ResultInterface`, `MailResult` and `createMailResultFromException()` use `Throwable` instead of `Exception`.
- **BC:** an exception thrown by a post-send listener propagates unchanged and is no longer reported as a send error, because the message was already delivered.
- **BC:** the `esmtp` transport is built directly from `smtp_options` instead of through a DSN string. Credentials with special characters work, empty credentials are not sent, and `smtp_options.name` and `connection_time_limit` now take effect. `SmtpOptions::getName()` returns `?string` and defaults to `null`.
- **BC:** a transport object or container service provided in `transport` is used as given and no longer replaced.
- **BC:** `sendmail` is the default transport when the `transport` key is omitted, matching `config/mail.global.php.dist` and the documentation.
- **BC:** `MailEvent::getResult()` returns `?ResultInterface`; the result is `null` in the pre-send event.
- **BC:** a message that never received a body fails validation instead of being sent empty; an empty `body.content` in the configuration is no longer applied.
- **BC:** the charset passed to `setBody()` and `body.charset` are used; `send()` only sets `utf-8` when no encoding is set.
- **BC:** the minimum supported PHP version is 8.3 (`~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.6.0`).
- A failing log write no longer turns a delivered email into an error; `LogService::sent()` returns `false` when the log directory cannot be created, and writes use `LOCK_EX`.
- Option getters fall back to defaults, so a minimal configuration no longer fails with uninitialized properties.

### Added

- `MailServiceInterface::reset()`, which restores the configured message and attachments. The `MailService` constructor accepts the default attachments as an optional fifth argument.
- `MailServiceInterface` is aliased to `dot-mail.service.default`, so it can be injected by interface.
- PHP 8.6 in the version constraint and in the CI matrix.
- `laminas/laminas-stdlib` as a direct dependency.

### Deprecated

- Nothing

### Removed

- Support for PHP 8.2.
- Unused attachment handling code from `Dot\Mail\Email`.

### Fixed

- `Email::addTo()` and the other address methods accept arrays of `Address` objects.
- Transport aliases are matched case-insensitively (`ESMTP`, `SendMail`).
- Missing `username`, `password` or `tls` keys in `smtp_options.connection_config` no longer raise warnings, and a username with special characters is encoded correctly.
- `canCreate()` no longer reads an undefined array key for names with too few parts.
- Detaching a listener that was never attached no longer removes a wrong entry.
- Error messages for an invalid transport, an invalid event listener and an invalid port are accurate.

## 5.4.3 - 2026-05-11

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Attachments of a failed send no longer leak into the next email; the message body is reset after every send attempt ([#115](https://github.com/dotkernel/dot-mail/pull/115))

## 5.4.2 - 2026-03-03

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- `MailService`: fixed the email structure when sending multiple attachments ([#113](https://github.com/dotkernel/dot-mail/pull/113))

## 5.4.1 - 2025-11-12

### Changed

- Updated OSS lifecycle metadata ([#111](https://github.com/dotkernel/dot-mail/pull/111))
- Updated readme and documentation ([#112](https://github.com/dotkernel/dot-mail/pull/112))

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 5.4.0 - 2025-11-03

### Changed

- Nothing

### Added

- Support for PHP 8.4 and 8.5 ([#104](https://github.com/dotkernel/dot-mail/pull/104))

### Deprecated

- Nothing

### Removed

- Support for PHP 8.1 ([#104](https://github.com/dotkernel/dot-mail/pull/104))

### Fixed

- Linking between documentation versions ([#105](https://github.com/dotkernel/dot-mail/pull/105))

## 5.1.6 - 2025-11-03

### Changed

- Nothing

### Added

- Support for PHP 8.4 and 8.5 ([#103](https://github.com/dotkernel/dot-mail/pull/103))

### Deprecated

- Nothing

### Removed

- Support for PHP 8.1 ([#103](https://github.com/dotkernel/dot-mail/pull/103))

### Fixed

- Nothing

## 5.0.4 - 2025-11-03

### Changed

- Documentation: version `5.0` is the default ([#75](https://github.com/dotkernel/dot-mail/pull/75))
- Updated the v5 documentation ([#78](https://github.com/dotkernel/dot-mail/pull/78))
- Bumped Psalm to 6.x ([#84](https://github.com/dotkernel/dot-mail/pull/84))

### Added

- Support for `laminas/laminas-servicemanager` 4.x ([#82](https://github.com/dotkernel/dot-mail/pull/82))
- Upgrade guide from v4 to v5 ([#81](https://github.com/dotkernel/dot-mail/pull/81))
- Support for PHP 8.4 and 8.5 ([#102](https://github.com/dotkernel/dot-mail/pull/102))

### Deprecated

- Nothing

### Removed

- Unused code inherited from version 4.0 ([#73](https://github.com/dotkernel/dot-mail/pull/73))
- Support for PHP 8.1 ([#102](https://github.com/dotkernel/dot-mail/pull/102))

### Fixed

- Version links in the documentation footer ([#80](https://github.com/dotkernel/dot-mail/pull/80))
- Attachments not working ([#87](https://github.com/dotkernel/dot-mail/pull/87))

## 5.3.0 - 2025-03-04

### Changed

- Replaced Psalm with PHPStan ([#90](https://github.com/dotkernel/dot-mail/pull/90))

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Unused code ([#97](https://github.com/dotkernel/dot-mail/pull/97))

### Fixed

- Error caused by special characters ([#94](https://github.com/dotkernel/dot-mail/pull/94))

## 5.1.5 - 2025-02-28

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Error caused by special characters in the password ([#95](https://github.com/dotkernel/dot-mail/pull/95))

## 5.1.4 - 2025-02-17

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Attachments not working ([#88](https://github.com/dotkernel/dot-mail/pull/88))

## 5.2.2 - 2025-02-14

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Attachments not working ([#87](https://github.com/dotkernel/dot-mail/pull/87))

## 5.2.1 - 2025-01-28

### Changed

- Bumped Psalm to 6.x ([#84](https://github.com/dotkernel/dot-mail/pull/84))

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 5.2.0 - 2025-01-13

### Changed

- Nothing

### Added

- Support for `laminas/laminas-servicemanager` 4.x ([#82](https://github.com/dotkernel/dot-mail/pull/82))

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 5.1.3 - 2025-01-06

### Changed

- Nothing

### Added

- Upgrade guide from v4 to v5 ([#81](https://github.com/dotkernel/dot-mail/pull/81))

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 5.1.2 - 2024-12-19

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Version links in the documentation footer ([#80](https://github.com/dotkernel/dot-mail/pull/80))

## 5.1.1 - 2024-12-19

### Changed

- Documentation: version `5.0` is the default ([#75](https://github.com/dotkernel/dot-mail/pull/75))
- Updated the v5 documentation ([#78](https://github.com/dotkernel/dot-mail/pull/78))

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 5.1.0 - 2024-12-06

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Unused code inherited from version 4.0 ([#73](https://github.com/dotkernel/dot-mail/pull/73))

### Fixed

- Nothing

## 5.0.3 - 2024-12-03

### Changed

- Added `declare(strict_types=1)` to the configuration file ([#69](https://github.com/dotkernel/dot-mail/pull/69))

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 5.0.2 - 2024-11-19

### Changed

- Cleaned the configuration file ([#67](https://github.com/dotkernel/dot-mail/pull/67))

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- The "from" name is displayed correctly ([#67](https://github.com/dotkernel/dot-mail/pull/67))

## 5.0.1 - 2024-11-18

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- SMTP transport: missing `port` array key ([#66](https://github.com/dotkernel/dot-mail/pull/66))

## 5.0.0 - 2024-11-15

### Changed

- Replaced `laminas-mail` with `symfony/mailer` ([#65](https://github.com/dotkernel/dot-mail/pull/65))
- Updated readme ([#61](https://github.com/dotkernel/dot-mail/pull/61))

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- `mime` and `imap` related functionality (see the upgrade guide from v4 to v5)

### Fixed

- Nothing

## 4.3.0 - 2024-10-28

### Changed

- Final release of the wrapper over `laminas-mail`
- Updated readme ([#61](https://github.com/dotkernel/dot-mail/pull/61))
- Locked PHP at version 8.3 ([#64](https://github.com/dotkernel/dot-mail/pull/64))

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 4.2.2 - 2024-06-28

### Changed

- Updated the documentation overview ([#59](https://github.com/dotkernel/dot-mail/pull/59))

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 4.2.1 - 2024-06-18

### Changed

- Updated the documentation overview ([#58](https://github.com/dotkernel/dot-mail/pull/58))

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 4.2.0 - 2024-05-03

### Changed

- Updated workflows ([#55](https://github.com/dotkernel/dot-mail/pull/55))

### Added

- v4 documentation ([#55](https://github.com/dotkernel/dot-mail/pull/55))

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 4.1.1 - 2023-11-29

### Changed

- Bumped PHP to 8.3 ([#53](https://github.com/dotkernel/dot-mail/pull/53))

### Added

- Codecov badge ([#51](https://github.com/dotkernel/dot-mail/pull/51))

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 4.1.0 - 2023-08-08

### Changed

- Psalm and phpcs fixes ([#47](https://github.com/dotkernel/dot-mail/pull/47))

### Added

- PHPUnit and tests ([#47](https://github.com/dotkernel/dot-mail/pull/47))

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 4.0.4 - 2022-09-28

### Changed

- Updated error reporting

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.4.6 - 2022-09-28

### Changed

- Updated error reporting

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 4.0.3 - 2022-08-31

### Changed

- Improved email storage handling
- Updated readme

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 4.0.2 - 2022-08-23

### Changed

- Updated mail.global.php.dist

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 4.0.1 - 2022-08-12

### Changed

- Updated readme
- Updated mail.global.php.dist

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 4.0.0 - 2022-08-11

### Changed

- Updated dependecies versions, including PHP to ~8.0.0 || ~8.1.0
- Restricted laminas-mail version to >=2.17

### Added

- Saving outgoing emails into folders

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.4.5 - 2022-08-30

### Changed

- Updated readme

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.4.4 - 2022-08-26

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Updated check for storage

## 3.4.3 - 2022-08-25

### Changed

- Improved email storage handling

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.4.2 - 2022-08-23

### Changed

- Updated mail.global.php.dist

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.4.1 - 2022-08-12

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- laminas/laminas-dependency-plugin

### Fixed

- Nothing

## 3.4.0 - 2022-08-10

### Changed

- Nothing

### Added

- Saving outgoing emails into folders

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.3.1 - 2022-08-10

### Changed

- Updated dependecies versions
- Restricted laminas-mail version to <=2.16

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.3.0 - 2022-06-20

### Changed

- Updated PHP version to allow 8.0 and 8.1

### Added

- PHP 8.0 and 8.1 compatiblity

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 2.1 - 2021-11-04

### Changed

- Nothing

### Added

- Logging sent emails

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.2.1 - 2021-09-24

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- log/mail directory privileges

## 3.2.0 - 2021-09-23

### Changed

- Nothing

### Added

- Logging sent emails

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.1.1 - 2021-06-08

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- fix issue #9: UTF-8 characters in message body

## 3.1.0 - 2020-11-03

### Changed

- Composer 2 compatibility.
- Update composer.json to require version 2 of laminas/laminas-dependency-plugin to be Composer 2 compatible.

### Added

- bump min version of laminas/laminas-mail

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- bump min version of laminas/laminas-dependency-plugin
- bump min version of dotkernel/dot-event

## 2.0.0 - 2020-01-30

### Changed

- Laminas and Mezzio migration.

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Support

### Fixed

- Nothing

## 1.0.0 - 2018-10-05

### Changed

- Nothing

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- TemplateRendererInterface requirement
- Templating in mail support

### Fixed

- Nothing

## 0.2.0 - 2018-05-14

### Changed

- updated `dot-twigrenderer` dependency

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 0.1.3 - 2017-04-21

### Changed

- send mail does not rethrow exceptions anymore

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 0.1.2 - 2017-03-16

### Changed

- updated composer.json

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 0.1.1 - 2017-03-11

Updated php file headers doc blocks

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 0.1.0 - 2017-03-09

Initial tagged release

### Added

- Everything

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing
