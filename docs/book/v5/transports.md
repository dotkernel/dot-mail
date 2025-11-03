# Transports

`dot-mail` can use any transport class that implements `Symfony\Component\Mailer\Transport\TransportInterface`, with the standard transport available being:

- `esmtp`
- `sendmail`

> Feel free to use any custom transport you desire, provided it implements the mentioned `TransportInterface`.

PHP's `mail()` function is a wrapper over `sendmail`, and as such has a different behavior on Windows than on *nix systems.
Using sendmail on Windows **will not work in combination with** `addBcc()`.

> Emails sent using the sendmail transport will be more often delivered to SPAM.

`esmtp` connects to the configured SMTP host to handle sending emails.
