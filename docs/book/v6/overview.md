# Overview

Dotkernel's mail service.

> dot-mail is a wrapper on top of [symfony mailer](https://github.com/symfony/mailer)

## Requirements

- PHP 8.3, 8.4, 8.5 or 8.6
- `symfony/mailer` 7.1.6 or newer, or 8.x
- any [PSR-11](https://www.php-fig.org/psr/psr-11/) container that accepts a factory per service key, for example `laminas/laminas-servicemanager`

## Version History

| Branch | Release          | PSR-11   | Mailer           | OSS Lifecycle                                                                                                                             | PHP Version                                                                                              |
|--------|------------------|----------|------------------|-------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------|
| 6.0    | `>=6.0.0`        | 1 \|\| 2 | `symfony/mailer` | ![OSS Lifecycle](https://img.shields.io/osslifecycle?file_url=https%3A%2F%2Fgithub.com%2Fdotkernel%2Fdot-mail%2Fblob%2F6.0%2FOSSMETADATA) | ![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-mail/6.0.0) |
| 5.2    | `>=5.2.0 <6.0.0` | 1 \|\| 2 | `symfony/mailer` | ![OSS Lifecycle](https://img.shields.io/osslifecycle?file_url=https%3A%2F%2Fgithub.com%2Fdotkernel%2Fdot-mail%2Fblob%2F5.2%2FOSSMETADATA) | ![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-mail/5.4.0) |
| 5.1    | `<5.2.0`         | 1        | `symfony/mailer` | ![OSS Lifecycle](https://img.shields.io/osslifecycle?file_url=https%3A%2F%2Fgithub.com%2Fdotkernel%2Fdot-mail%2Fblob%2F5.1%2FOSSMETADATA) | ![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-mail/5.1.6) |
| 5.0    | `<5.1.0`         | 1 \|\| 2 | `symfony/mailer` | ![OSS Lifecycle](https://img.shields.io/osslifecycle?file_url=https%3A%2F%2Fgithub.com%2Fdotkernel%2Fdot-mail%2Fblob%2F5.0%2FOSSMETADATA) | ![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-mail/5.0.4) |
| 4.0    | `<=4.3.0`        | 1        | `laminas-mail`   | ![OSS Lifecycle](https://img.shields.io/osslifecycle?file_url=https%3A%2F%2Fgithub.com%2Fdotkernel%2Fdot-mail%2Fblob%2F4.0%2FOSSMETADATA) | ![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-mail/4.3.0) |

## Badges

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/dot-mail)
![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-mail/6.0.0)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/blob/6.0/LICENSE.md)

[![Build Static](https://github.com/dotkernel/dot-mail/actions/workflows/continuous-integration.yml/badge.svg?branch=6.0)](https://github.com/dotkernel/dot-mail/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/dotkernel/dot-mail/branch/6.0/graph/badge.svg?token=G51NEHYKD3)](https://codecov.io/gh/dotkernel/dot-mail)
[![PHPStan](https://github.com/dotkernel/dot-mail/actions/workflows/static-analysis.yml/badge.svg?branch=6.0)](https://github.com/dotkernel/dot-mail/actions/workflows/static-analysis.yml)

## Features

- Several mail services in one application, each with its own defaults, transport and listeners.
- Transports: `sendmail`, `esmtp`, any class implementing `Symfony\Component\Mailer\Transport\TransportInterface`, or a transport service from the container.
- Attachments from file paths or from a directory scanned at send time.
- Events before sending, after sending and on errors.
- An optional log of every sent email.
