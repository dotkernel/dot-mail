# Installation

`dot-mail` 6 requires PHP 8.3 or newer.

Install `dotkernel/dot-mail` by executing the following Composer command:

```shell
composer require dotkernel/dot-mail
```

Register the package by adding `Dot\Mail\ConfigProvider::class` to your configuration aggregator, for example in `config/config.php`:

```php
$aggregator = new ConfigAggregator([
    // ...
    \Dot\Mail\ConfigProvider::class,
    // ...
]);
```

Then copy `vendor/dotkernel/dot-mail/config/mail.global.php.dist` into your project's `config/autoload/` directory as `mail.global.php`, and fill it in as described in [Configuration](configuration.md).
Keep credentials, such as the SMTP password, in a `mail.local.php` file that is not committed.

The factories only use `Psr\Container\ContainerInterface`, so they work with any PSR-11 container that accepts a factory per service key.
Applications that use Laminas Service Manager, such as Mezzio applications, need nothing extra.
