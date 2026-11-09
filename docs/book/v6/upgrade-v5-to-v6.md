# Upgrading from v5 to v6

Version 6 requires PHP 8.3 or newer.
In your `composer.json`, require `"dotkernel/dot-mail": "^6.0"` and run `composer update dotkernel/dot-mail`.

Then go through the sections below. The [table of breaking changes](#breaking-changes) lists every change that can affect your code.

## Moving the services under `services`

Mail services are now configured under `dot_mail.services`. The `log` key stays where it is.

Before:

```php
return [
    'dot_mail' => [
        'default' => [
            'message_options' => [/* ... */],
            'transport'       => 'esmtp',
            'smtp_options'    => [/* ... */],
        ],
        'log'     => [
            'sent' => getcwd() . '/log/mail/sent.log',
        ],
    ],
];
```

After:

```php
return [
    'dot_mail' => [
        'services' => [
            'default' => [
                'message_options' => [/* ... */],
                'transport'       => 'esmtp',
                'smtp_options'    => [/* ... */],
            ],
        ],
        'log'      => [
            'sent' => getcwd() . '/log/mail/sent.log',
        ],
    ],
];
```

A configuration in the old layout fails with: `Mail service "default" must be configured under "dot_mail.services"`.
Compare with the new `config/mail.global.php.dist`.

## Replacing the abstract factories

`Dot\Mail\Factory\AbstractMailFactory`, `MailServiceAbstractFactory` and `MailOptionsAbstractFactory` were removed, together with the `abstract_factories` entry of the `ConfigProvider`.
The new `MailServiceFactory` and `MailOptionsFactory` are plain PSR-11 factories, so `dot-mail` no longer requires `laminas/laminas-servicemanager`.

The `ConfigProvider` registers `dot-mail.service.default` and `dot-mail.options.default`. If you only use the default service, there is nothing to change.

Every other mail service has to be registered. Before, the abstract factory resolved any `dot-mail.service.<name>` configured under `dot_mail`:

```php
$newsletter = $container->get('dot-mail.service.newsletter');
```

After, register the key with the factory, then get it as before:

```php
return [
    'dependencies' => [
        'factories' => [
            'dot-mail.service.newsletter' => \Dot\Mail\Factory\MailServiceFactory::class,
            // only if you get the options themselves from the container:
            'dot-mail.options.newsletter' => \Dot\Mail\Factory\MailOptionsFactory::class,
        ],
    ],
];
```

If your own code listed one of the removed classes, for example in an `abstract_factories` configuration, remove that entry.

## Sending

Set the recipients, subject, body and attachments before every `send()`: the service restores the configured defaults after each attempt.

`send()` never returns an invalid result. Replace checks of `isValid()` with a `try`/`catch`:

```php
use Dot\Mail\Exception\MailException;

try {
    $this->mailService->send();
} catch (MailException $exception) {
    error_log($exception->getPrevious()?->getMessage() ?? $exception->getMessage());
}
```

## Breaking changes

| Change | Symptom | Fix |
| --- | --- | --- |
| Services under `dot_mail.services` | `InvalidArgumentException`: `Mail service "default" must be configured under "dot_mail.services"` | Move each service block under `services`; keep `log` where it is |
| Abstract factories removed | Service not found for `dot-mail.service.<name>` other than `default`; class not found for `MailServiceAbstractFactory` | Register `'dot-mail.service.<name>' => MailServiceFactory::class` |
| `send()` resets after each attempt | A second email has no recipients, subject or body | Set them before every `send()` |
| `send()` throws instead of returning an invalid result | `isValid()` checks are never `false` | Catch `MailException` |
| `send()` handles `Throwable`; `ResultInterface`, `MailResult` and `createMailResultFromException()` use `Throwable` | Your implementation of `ResultInterface` fails to load | Change `Exception` to `Throwable` in it |
| Post-send listener exceptions propagate | An exception reaches the caller after the email was sent | Catch it in the listener or around `send()` |
| A missing attachment throws | `MailException` instead of a silently skipped file | Check paths before `addAttachment()` |
| Attachment directory read at send time; dotfiles and dot-directories skipped | Different attachments than before | Move files the email needs out of hidden directories |
| `sendmail` is the default transport | Mail goes through sendmail when `transport` is omitted | Set `transport` explicitly |
| `esmtp` built from `smtp_options` instead of a DSN | `smtp_options.name` and `connection_time_limit` now take effect | Check these values |
| A transport object or service is used as given | Your transport is no longer replaced | Remove workarounds |
| An empty body fails validation; empty `body.content` is not applied | `MailException`: `A message must have a text or an HTML part.` | Set a body |
| `setBody()` charset and `body.charset` are used | A different `Content-Type` charset | Pass the charset you need |
| `MailEvent::getResult()` returns `?ResultInterface` | Null access in a pre-send listener | Check for `null` |
| `MailServiceInterface::setBody()` accepts `string\|AbstractPart` | Fatal error in your implementation of the interface | Widen the parameter |
| `MailEvent` extends `Laminas\EventManager\Event` instead of `Dot\Event\Event` | `instanceof Dot\Event\Event` is `false` | Check against `MailEvent` or `Laminas\EventManager\EventInterface` |
| `Email::setSender()` parameter renamed from `$addresses` to `$address` | A named argument `addresses:` fails | Use `address:` or a positional argument |
| `SmtpOptions::getName()` returns `?string`, `null` by default | Type error in code expecting `string` | Handle `null` |
| An unconfigured mail service throws | `InvalidArgumentException`: `Mail service "<name>" is not configured under "dot_mail.services"` | Configure the service |
| PHP 8.3 minimum | Composer refuses to install | Upgrade PHP |
