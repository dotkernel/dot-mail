# Transports

`dot-mail` sends through any class implementing `Symfony\Component\Mailer\Transport\TransportInterface`.
The `transport` key of a mail service accepts:

| Value | Result |
| --- | --- |
| `sendmail` (default) | `Symfony\Component\Mailer\Transport\SendmailTransport` |
| `esmtp` | `Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport`, built from `smtp_options` |
| A container service name | The service, which must return a `TransportInterface` |
| A class name | A new instance of the class, created without constructor arguments |
| A `TransportInterface` object | The object, used as given |

## sendmail

`sendmail` hands messages to the local sendmail binary.
It needs a working mail transfer agent on the server, and messages sent this way are more often classified as spam.

## esmtp

`esmtp` connects to the SMTP server configured in `smtp_options`; see [Configuration](configuration.md#smtp-options).

## Custom transports

Any other Symfony transport, for example one from a `symfony/*-mailer` bridge, can be registered as a container service and referenced by name:

```php
return [
    'dependencies' => [
        'factories' => [
            'mail.transport.api' => \App\Mail\ApiTransportFactory::class,
        ],
    ],
    'dot_mail' => [
        'services' => [
            'default' => [
                'transport' => 'mail.transport.api',
            ],
        ],
    ],
];
```

The service can also be replaced for a single mail service at runtime with `setTransport()`.
