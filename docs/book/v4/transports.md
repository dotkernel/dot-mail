# Transports

`dot-mail` can use any transport class that implements `Laminas\Mail\Transport\TransportInterface`, with the four standard transports available being:

- `Laminas\Mail\Transport\Sendmail` - the default transport set in the config file
- `Laminas\Mail\Transport\Smtp`
- `Laminas\Mail\Transport\File`
- `Laminas\Mail\Transport\InMemory`

> Feel free to use any custom transport you desire, provided it implements the mentioned `TransportInterface`.

`Sendmail` is a wrapper over PHP's `mail()` function, and as such has a different behavior on Windows than on *nix systems. Using sendmail on Windows **will not work in combination with** `addBcc()`.

- Note: emails sent using the sendmail transport will be more often delivered to SPAM.

`Smtp` connects to the configured SMTP host to handle sending emails.
Saving a copy of outgoing mail into a folder is possible for this transport only, and is done by uncommenting `save_sent_message_folder` in the config file `mail.local.php` under `dot_mail.default`.

- Common folder names are `INBOX`, `INBOX.Archive`, `INBOX.Drafts`, `INBOX.Sent`, `INBOX.Spam`, `INBOX.Trash`.
  If you have `MailService` available in your class, you can call `$this->mailService->getFolderGlobalNames()` to list the folder global names for the email you are using.
- Multiple folders can be added to the `save_sent_message_folder` key to save a copy of the outgoing email in each folder.

`File` writes each message individually in a file named after the configured format, placed in the configured directory.
From here the files may be used for sending via another transport mechanism, or simply as logs.

`InMemory` saves the message in memory, allowing access to the last "sent" message via the `getLastMessage()` function.

- As the email is not sent, this transport can be helpful in development, with the access to the message being potentially useful in tests as well.

```php
$this->mailService->setBody('First email body');
$this->mailService->setSubject('First email subject');
$this->mailService->getMessage()->setTo('email@example.com');
// The email is not sent to the email, instead it is stored in memory
$this->mailService->send();

$this->mailService->setBody('Second email body');
$this->mailService->setSubject('Second email subject');
$this->mailService->getMessage()->setTo('email@example.com');

// The email is not sent to the email either, it overwrites the previously "sent" email in memory
$this->mailService->send();

// Returns the second email's Laminas\Mail\Message object from memory
$lastMessage = $this->mailService()->getTransport()->getLastMessage();
```
