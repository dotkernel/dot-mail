# Configuration

Register `dot-mail` in you project by adding `Dot\Mail\ConfigProvider::class` to your configuration aggregator (to `config/config.php` for example).
After registering the `ConfigProvider` copy the configuration file `config/mail.global.php.dist` into your project's `config/autoload/` directory as `mail.global.php`.

The resulting `mail.global.php` contains the necessary configurations for all available transport types, message options and logging options in one place.
The config file provides a set of default values available to all mails under the `dot-mail.default` key.

Many of these options can be programmatically set when sending the actual email.

An example of this is the `dot-mail.default.message_options` key, which can be filled in with default message values to be available to all emails.
When sending the actual email, these values can be overwritten by using an available "setter" function, supplemented by using "add" functions or simply left as the default.

```php
// setter will overwrite existing destination email
$this->mailService->getMessage()->setTo("receiver@email.com");

// existing destination email kept, new destination email added alongside it
$this->mailService->getMessage()->addTo("receiver@email.com");
```

## Transport configuration

`dot-mail` uses the `transport` key under the main `dot_mail` configuration key to select the email transport.
It has two email transport classes available (by default `sendmail`), one of which is to be added under the `dot_mail.transport` key for use.

Sending email with the `esmtp` transport requires valid data for the values under `dot-mail.default.smtp_options`, which is only used in this case.

> The configured path must be a writable directory

## Logging configuration

Uncommenting the `dot-mail.log` key will save a copy of all sent emails' subject, recipient addresses, cc and bcc addresses alongside a timestamp.
In order to enable it, make sure that your `mail.local.php` has the below `log` configuration under the `dot_mail` key:

```php
<?php

return [
    'dot_mail' => [
        ...
        'log' => [
            'sent' => getcwd() . '/log/mail/sent.log'
        ]
    ]
];
```

To disable it, set the value of `sent` to `null`.
