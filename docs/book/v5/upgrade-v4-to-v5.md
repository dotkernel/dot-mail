# How to update `dotkernel/dot-mail` from v3/v4 to v5 in your projects

- The first thing to do is download the new [mail configuration file](https://github.com/dotkernel/dot-mail/blob/5.0/config/mail.global.php.dist).
- Add the values you configured for your project, focusing on `transport`, `message_options` and `smtp_options`, then replace your old configuration file.
- In your `composer.json` update `"dotkernel/dot-mail": "^5.0.0",` and run `composer update` in the command line.

At this moment, `mime` and `imap` related functionality is removed.

## Technical approach

You can follow all the changes in this list of PRs:

- [dot-mail PR 65](https://github.com/dotkernel/dot-mail/pull/65/files)
- [dot-mail PR 66](https://github.com/dotkernel/dot-mail/pull/66/files)
- [dot-mail PR 67](https://github.com/dotkernel/dot-mail/pull/67/files)
- [dot-mail PR 69](https://github.com/dotkernel/dot-mail/pull/69/files)

> Function definition changes will not be covered in this article.

When upgrading DotMail from v4 to v5, the main focus is on the configuration file `mail.global.php`.
It was revised to implement symfony/mailer, to remove features that are no longer available and to make DotMail easier to configure.

```php
declare(strict_types=1);

return [
    /**
     * Dotkernel mail module configuration.
     * Note that many of these options can be set programmatically too;
     * when sending mail messages, actually that is what you'll usually do,
     * these configs provide just defaults and options that remain the same for all mails
     */
    'dot_mail' => [
        //the key is the mail service name, this is the default one, which does not extend any configuration
        'default' => [
            //message configuration
            'message_options' => [
                //from email address of the email
                'from' => '',
                //from name to be displayed instead of from address
                'from_name' => '',
                //reply-to email address of the email
                'reply_to' => '',
                //replyTo name to be displayed instead of the address
                'reply_to_name' => '',
                //destination email address as string or a list of email addresses
                'to' => [],
                //copy destination addresses
                'cc' => [],
                //hidden copy destination addresses
                'bcc' => [],
                //email subject
                'subject' => '',
                //body options - content can be plain text, HTML
                'body' => [
                    'content' => '',
                    'charset' => 'utf-8',
                ],
                //attachments config
                'attachments' => [
                    'files' => [],
                    'dir'   => [
                        'iterate'   => false,
                        'path'      => 'data/mail/attachments',
                        'recursive' => false,
                    ],
                ],
            ],
            /**
             * the mail transport to use can be any class implementing
             * Symfony\Component\Mailer\Transport\TransportInterface
             *
             * for standard mail transports, you can use these aliases:
             * - sendmail  => Symfony\Component\Mailer\Transport\SendmailTransport
             * - esmtp     => Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport
             *
             * defaults to sendmail
             **/
            'transport' => 'sendmail',
            //options that will be used only if esmtp adapter is used
            'smtp_options' => [
                //hostname or IP address of the mail server
                'host' => '',
                //port of the mail server - 587 or 465 for secure connections
                'port'              => 587,
                'connection_config' => [
                    //the smtp authentication identity
                    'username' => '',
                    //the smtp authentication credential
                    'password' => '',
                    //to disable auto_tls set tls key to false
                    //it's not recommended to disable TLS while connecting to an SMTP server
                    'tls' => null,
                ],
            ],
        ],
        // option to log the SENT emails
        'log' => [
            'sent' => getcwd() . '/log/mail/sent.log',
        ],
    ],
];
```

Make sure to use **ONE** of the below transporters, based on your server configuration.

```php
'transport' => 'sendmail',
```

OR

```php
'transport' => 'esmtp',
```

We set **Sendmail** to be the default.
