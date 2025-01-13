# dot-mail

> [!IMPORTANT]
> dot-mail is a wrapper on top of [symfony mailer](https://github.com/symfony/mailer)

## dot-mail badges

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/dot-mail)
![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-mail/5.2.0)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/blob/5.0/LICENSE.md)

[![Build Static](https://github.com/dotkernel/dot-mail/actions/workflows/continuous-integration.yml/badge.svg?branch=5.0)](https://github.com/dotkernel/dot-mail/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/dotkernel/dot-mail/branch/5.0/graph/badge.svg?token=G51NEHYKD3)](https://codecov.io/gh/dotkernel/dot-mail)

## Installation

Install `dotkernel/dot-mail` by executing the following Composer command:

```shell
composer require dotkernel/dot-mail
```

## Configuration

### Mail - Sendmail

If your server has Sendmail installed, update the `config/autoload/mail.local.php.dist` file by setting the `transport` key like below

```php
<?php
return [
    'dot_mail' => [
        'default' => [
            //...
            'transport' => 'sendmail',
            //...
        ]
    ]
]
```

### Mail - ESMTP

If you want your application to send mails on e.g. registration, contact, then edit the file `config/autoload/mail.local.php`.  Set the `transport`, `message_options` and `smtp_options` keys like below.

Under `message_options` key:

- `from` - email address from whom users will receive emails

Under `smtp_options` key:

- `host` - the mail server's hostname or IP address
- `port` - the mail server's port
- `connection_config` - fill in the `username` and `password` keys with the login details of the email used in `from` above
- if you want to disable auto_tls set `tls` key to false

> Note: all other keys can be left as is.

```php
<?php
return [
    'dot_mail' => [
        'default' => [
            //...
            'transport' => 'esmtp'
            'message_options' => [
                'from' => '',
                //...
            ],
            'smtp_options' => [
                'host' => '',
                'port' => 25,
                'connection_config' => [
                    'username' => '',
                    'password' => '',
                    'tls' => null,
                ]
            ]
            //...
        ]
    ]
]
```

In `config/autoload/local.php` add under `contact` => `message_receivers` => `to` key *string* values with the emails that should receive contact messages

> Note: **Please add at least 1 email address in order for contact message to reach someone**

Also feel free to add as many cc as you want under `contact` => `message_receivers` => `cc` key

### Sending an e-mail

Below is an example of how to use the email in the most basic way. You can add your own code to it e.g. to get the user data from a User object or from a config file, to use a template for the body.

Note that `addTo` is only one of the methods available for the `Message` class returned by `getMessage()`. Other useful methods that were not included in the example are `addCc()`, `addBcc()`, `addReplyTo()`.

The returned type is boolean, but if the `isValid()` method is removed, the returned type becomes `MailResult` which allows the use of `getMessage()` for a more detailed error message. See the `Testing if an e-mail message is valid` section below.

```php
public function sendBasicMail()
{
    $this->mailService->setBody('Email body');
    $this->mailService->setSubject('Email subject');
    $this->mailService->getMessage()->addTo('email@example.com', 'User name');
    $this->mailService->getMessage()->setEncoding('utf-8');
    return $this->mailService->send()->isValid();
}
```

It's optional, but recommended to call the above function in a `try-catch` block to display helpful error messages. The next example calls the `sendBasicMail` function from within `UserController`, but you can implement it in other controllers, just make sure that the controller's construct also includes the `FlashMessenger` parameter `$messenger`.

```php
try {
    $this->userService->sendBasicMail();
    $this->messenger->addSuccess('The mail was sent successfully', 'user-login');
    //more code...
} catch (Exception $exception) {
    $this->messenger->addError($exception->getMessage(), 'user-login');
    //more code...
}
```

### Testing if an e-mail message is valid

After sending an e-mail you can check if the message was valid or not.
The `$this->mailService->send()->isValid()` method call will return a boolean value.
If the returned result is `true`, the e-mail was valid, otherwise the e-mail was invalid.
In case your e-mail was invalid, you can check for any errors using `$this->mailService->send()->getMessage()`.

Using the below logic will let you determine if a message was valid or not and log it.
You can implement your own custom error logging logic.

```php
$result = $this->mailService->send();
if (! $result->isValid()) {
    //log the error
    error_log($result->getMessage());
}
```

> Invalid e-mail messages will not be sent.

### Logging outgoing emails

Optionally, you can keep a log of each successfully sent email. This might be useful when you need to know if/when a specific email has been sent out to a recipient.

Logs are stored in the following format:

```text
[YYYY-MM-DD HH:MM:SS]: {"subject":"Test subject","to":["Test Account <test@dotkernel.com>"],"cc":[],"bcc":[]}.
```

In order to enable it, make sure that your `config/autoload/mail.local.php` has the below `log` configuration under the `dot_mail` key:

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
