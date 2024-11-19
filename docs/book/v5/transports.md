# Transports

`dot-mail` can use any transport class that implements `Symfony\Component\Mailer\Transport\TransportInterface`, with the standard transport available being:

- `Symfony\Component\Mailer\Transport\Smtp\SmtpTransport,`
- `Symfony\Component\Mailer\Transport\SendmailTransport,`

> Feel free to use any custom transport you desire, provided it implements the mentioned `TransportInterface`.

PHP's `mail()` function is a wrapper over `Sendmail`, and as such has a different behaviour on Windows than on *nix systems. Using sendmail on Windows **will not work in combination with** `addBcc()`.

- Note: emails sent using the sendmail transport will be more often delivered to SPAM.

`Smtp` connects to the configured SMTP host in order to handle sending emails.
