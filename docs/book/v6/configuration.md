# Configuration

All configuration lives under the `dot_mail` key, usually in `config/autoload/mail.global.php`, copied from `config/mail.global.php.dist`.

```php
return [
    'dot_mail' => [
        'log'      => [
            'sent' => getcwd() . '/log/mail/sent.log',
        ],
        'services' => [
            'default' => [
                'message_options' => [/* ... */],
                'transport'       => 'sendmail',
                'smtp_options'    => [/* ... */],
                'event_listeners' => [],
            ],
        ],
    ],
];
```

- `dot_mail.services` holds one block per mail service, indexed by the service name.
- `dot_mail.log` is shared by all services and stays outside `services`.

> A configuration in the 5.x layout, with services directly under `dot_mail`, fails with an `InvalidArgumentException`.
> See [Upgrading from v5 to v6](upgrade-v5-to-v6.md).

## Mail services

The `default` service is registered by `Dot\Mail\ConfigProvider` as `dot-mail.service.default`, and `Dot\Mail\Service\MailServiceInterface` is an alias of it.

To use another service, add its block under `dot_mail.services` and register it in your dependencies with `Dot\Mail\Factory\MailServiceFactory`:

```php
return [
    'dependencies' => [
        'factories' => [
            'dot-mail.service.newsletter' => \Dot\Mail\Factory\MailServiceFactory::class,
        ],
    ],
    'dot_mail' => [
        'services' => [
            'default'    => [/* ... */],
            'newsletter' => [/* ... */],
        ],
    ],
];
```

The options of a service are available as `dot-mail.options.<name>` when registered with `Dot\Mail\Factory\MailOptionsFactory`; `dot-mail.options.default` is registered by the `ConfigProvider`.

## Message options

`message_options` holds the defaults of every message sent by the service:

| Key | Type | Description |
| --- | --- | --- |
| `from`, `from_name` | string | Sender address and display name |
| `reply_to`, `reply_to_name` | string | Reply-to address and display name |
| `to`, `cc`, `bcc` | list of strings | Default recipients |
| `subject` | string | Default subject |
| `body.content` | string | Default HTML body; an empty string sets no body |
| `body.charset` | string | Charset of the default body, `utf-8` by default |
| `attachments.files` | list of paths | Files attached to every message |
| `attachments.dir` | array | Directory scanned for attachments, see below |

Each value can be changed for a single message through the mail service, see [Usage](usage.md):

```php
// replaces the configured recipients
$mailService->getMessage()->setTo('receiver@example.com');

// keeps the configured recipients and adds one
$mailService->getMessage()->addTo('receiver@example.com');
```

The service restores these defaults after every `send()`.

### Attachments directory

```php
'attachments' => [
    'files' => [],
    'dir'   => [
        'iterate'   => true,
        'path'      => 'data/mail/attachments',
        'recursive' => false,
    ],
],
```

When `iterate` is `true`, every file in `path` is attached; with `recursive`, files in subdirectories too.
The directory is read at send time, so files added later are included.
Files and directories whose name starts with a dot are skipped.
A relative `path` is resolved against the current working directory.

## Transport

`transport` selects how messages are delivered. It can be:

- `sendmail` (the default when the key is omitted) or `esmtp`, case-insensitive
- the name of a class implementing `Symfony\Component\Mailer\Transport\TransportInterface`, created without arguments
- the name of a container service that returns a `TransportInterface`
- a `TransportInterface` object, used as given

See [Transports](transports.md).

### SMTP options

`smtp_options` is used only by the `esmtp` transport:

```php
'smtp_options' => [
    'host'                  => 'smtp.example.com',
    'port'                  => 587,
    'name'                  => 'client.example.com',
    'connection_time_limit' => 30,
    'connection_config'     => [
        'username' => 'user@example.com',
        'password' => 'secret',
        'tls'      => null,
    ],
],
```

| Key | Default | Description |
| --- | --- | --- |
| `host` | `127.0.0.1` | Hostname or IP address of the mail server; set it for any real server |
| `port` | `25` | `587` for submission with STARTTLS, `465` for implicit TLS |
| `name` | transport default | Local client name sent in the `EHLO` greeting |
| `connection_time_limit` | transport default | Socket timeout in seconds |
| `connection_config.username`, `password` | none | Credentials; empty values send no authentication; special characters need no encoding |
| `connection_config.tls` | `null` | `null` lets the transport choose (STARTTLS when offered, implicit TLS on port 465); `false` disables TLS |

> It's not recommended to disable TLS while connecting to an SMTP server over the Internet.

## Event listeners

`event_listeners` lists the container services that receive the mail events, either as a service name or with a priority:

```php
'event_listeners' => [
    \App\Mail\AuditListener::class,
    ['type' => \App\Mail\MetricsListener::class, 'priority' => 10],
],
```

Each service must implement `Dot\Mail\Event\MailEventListenerInterface`; see [Usage](usage.md#events).
An entry of any other shape fails with a `Dot\Mail\Exception\RuntimeException`.

## Logging

```php
'dot_mail' => [
    'log' => [
        'sent' => getcwd() . '/log/mail/sent.log',
    ],
],
```

`log.sent` is the file that receives one line per sent email; missing directories are created.
Set it to `null` to disable logging.
