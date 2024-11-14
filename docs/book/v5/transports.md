# Transports

`dot-mail` can use any transport class that implements `symfony\Component\Mailer\Transport\TransportInterface`, with the standard transport available being:

- `Laminas\Mail\Transport\Smtp`

- Note: feel free to use any custom transport you desire, provided it implements the mentioned `TransportInterface`.

`Smtp` connects to the configured SMTP host in order to handle sending emails.

- As the email is not sent, this transport can be helpful in development, with the access to the message being potentially useful in tests as well

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
```
