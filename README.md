# dot-mail

Dotkernel's mail service.

> dot-mail is a wrapper on top of [symfony mailer](https://github.com/symfony/mailer)

## Documentation

Documentation is available at: https://docs.dotkernel.org/dot-mail/.

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

## Installation

Install `dotkernel/dot-mail` by executing the following Composer command:

```shell
composer require dotkernel/dot-mail
```

## Configuration

Register `Dot\Mail\ConfigProvider::class` in your configuration aggregator, then copy `config/mail.global.php.dist` into your project's `config/autoload/` directory as `mail.global.php`.

Mail services are configured under `dot_mail.services`, one block per service. The `default` service is registered by the `ConfigProvider`; register any other one with `Dot\Mail\Factory\MailServiceFactory`.
The factories work with any PSR-11 container that accepts a factory per service key, so Laminas Service Manager users need nothing extra.

### Mail - Sendmail

If your server has Sendmail installed, set the `transport` key like below:

```php
<?php

return [
    'dot_mail' => [
        'services' => [
            'default' => [
                //...
                'transport' => 'sendmail',
                //...
            ],
        ],
    ],
];
```

### Mail - ESMTP

To send through an SMTP server, set the `transport`, `message_options` and `smtp_options` keys like below, preferably in a `config/autoload/mail.local.php` file that is not committed.

Under the `message_options` key:

- `from` - email address from whom users will receive emails

Under the `smtp_options` key:

- `host` - the mail server's hostname or IP address
- `port` - the mail server's port
- `connection_config` - fill in the `username` and `password` keys with the login details of the email used in `from` above
- to disable TLS, set the `tls` key to `false`

```php
<?php

return [
    'dot_mail' => [
        'services' => [
            'default' => [
                //...
                'transport'       => 'esmtp',
                'message_options' => [
                    'from' => 'sender@example.com',
                    //...
                ],
                'smtp_options'    => [
                    'host'              => 'smtp.example.com',
                    'port'              => 587,
                    'connection_config' => [
                        'username' => 'sender@example.com',
                        'password' => 'secret',
                        'tls'      => null,
                    ],
                ],
                //...
            ],
        ],
    ],
];
```

### Sending an e-mail

```php
public function sendBasicMail(): void
{
    $this->mailService->setBody('<p>Email body</p>');
    $this->mailService->setSubject('Email subject');
    $this->mailService->getMessage()->addTo('email@example.com', 'User name');
    $this->mailService->send();
}
```

`$this->mailService` is a `Dot\Mail\Service\MailServiceInterface`.
After every `send()` the service restores the configured defaults, so set the recipients, subject and body again for every message.

`send()` throws `Dot\Mail\Exception\MailException` when the message is invalid or cannot be sent; the original exception is available through `getPrevious()`:

```php
use Dot\Mail\Exception\MailException;

try {
    $this->userService->sendBasicMail();
    $this->messenger->addSuccess('The mail was sent successfully', 'user-login');
} catch (MailException $exception) {
    error_log($exception->getPrevious()?->getMessage() ?? $exception->getMessage());
    $this->messenger->addError('The mail could not be sent', 'user-login');
}
```

### Logging outgoing emails

Optionally, you can keep a log of each successfully sent email.
Logs are stored in the following format:

```text
[YYYY-MM-DD HH:MM:SS]: {"subject":"Test subject","to":["Test Account <test@dotkernel.com>"],"cc":[],"bcc":[]}
```

To enable it, set the `log` key under `dot_mail`, next to `services`:

```php
<?php

return [
    'dot_mail' => [
        'log'      => [
            'sent' => getcwd() . '/log/mail/sent.log',
        ],
        'services' => [
            //...
        ],
    ],
];
```

To disable it, set the value of `sent` to `null`.

### Upgrading from 5.x

See the [upgrade guide](https://docs.dotkernel.org/dot-mail/v6/upgrade-v5-to-v6/).
