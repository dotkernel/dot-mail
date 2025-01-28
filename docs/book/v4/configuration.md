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
It has four email transport classes available by default (`Sendmail`, `Smtp`, `File`, `InMemory`), one of which is to be added under the `dot_mail.transport` key for use.

Both `Sendmail` and `InMemory` transports requires no specific configuration to use by default.

Sending email with the `Smtp` transport requires valid data for the values under `dot-mail.default.smtp_options`, which is only used in this case.
The `dot_mail.default.save_sent_message_folder` key may be uncommented when using this transport, saving a copy of all sent email to a certain IMAP folder.

Using `Laminas\Mail\Transport\File` as the transport will require uncommenting the `dot-mail.default.file_options` key.

> The configured path must be a writable directory

```php
'file_options' => [
    'path' => 'data/mail/output',
    //'callback' => null,
],
```

- If no callback is provided to specify a new file name format, the messages will be saved in files using the default format set in `Laminas\Mail\Transport\FileOptions`.

```php
// Example of a custom format
'callback' => static fn() => sprintf('DotMail%d_%s.log', time(), 'customFormat'),
```

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
