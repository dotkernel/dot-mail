# Usage

## Getting the mail service

Inject `Dot\Mail\Service\MailServiceInterface` to use the `default` mail service:

```php
use Dot\Mail\Service\MailServiceInterface;

class UserService
{
    public function __construct(
        private MailServiceInterface $mailService,
    ) {
    }
}
```

For another configured service, get `dot-mail.service.<name>` from the container, for example `dot-mail.service.newsletter`.
See [Configuration](configuration.md#mail-services) for how to register it.

## Sending an e-mail

```php
public function sendBasicMail(): void
{
    $this->mailService->setBody('<p>Email body</p>');
    $this->mailService->setSubject('Email subject');
    $this->mailService->getMessage()->addTo('email@example.com', 'User name');
    $this->mailService->send();
}
```

`getMessage()` returns a `Dot\Mail\Email`, which also has `addCc()`, `addBcc()`, `addReplyTo()`, `setFrom()`, `setSender()` and `priority()`.
The address methods accept a string, a `Symfony\Component\Mime\Address`, or an array of either.

`send()` returns a valid `Dot\Mail\Result\ResultInterface` when the message was handed to the transport.
When the message is invalid (for example without recipients or without a body) or the transport fails, `send()` throws `Dot\Mail\Exception\MailException`.
The original exception is available through `getPrevious()`:

```php
use Dot\Mail\Exception\MailException;

try {
    $this->userService->sendBasicMail();
    $this->messenger->addSuccess('The mail was sent successfully', 'user-login');
} catch (MailException $exception) {
    error_log($exception->getPrevious()?->getMessage() ?? $exception->getMessage());
    $this->messenger->addError('The mail could not be sent', 'user-login');
}
```

### Every send starts from the configured defaults

After every `send()`, successful or not, the service restores the message and the attachments configured in `message_options`.
Set the recipients, subject, body and attachments again for every message.
`getMessage()` returns a new object after each send, so do not keep a reference to it.

`reset()` restores the defaults without sending.

## Body

```php
// HTML body in utf-8
$this->mailService->setBody('<p>Hello</p>');

// HTML body in another charset
$this->mailService->setBody('<p>Hello</p>', 'iso-8859-1');

// any MIME part, used as given; the charset argument is ignored
$this->mailService->setBody(new \Symfony\Component\Mime\Part\TextPart('Plain text body'));
```

A plain-text alternative can be added with `getMessage()->text('Plain text version')`.
A message without any body fails validation.

## Attachments

```php
$this->mailService->addAttachment('/path/to/invoice.pdf');

// attached as "ticket.pdf"
$this->mailService->addAttachment('/path/to/file-123.pdf', 'ticket.pdf');

$this->mailService->addAttachments(['/path/to/a.txt', '/path/to/b.txt']);
```

Attachments configured in `message_options.attachments` are added to every message.
A file that does not exist or cannot be read makes `send()` throw a `MailException`.

## Events

Listeners extend `Dot\Mail\Event\AbstractMailEventListener` and override the events they need:

```php
use Dot\Mail\Event\AbstractMailEventListener;
use Dot\Mail\Event\MailEvent;

class AuditListener extends AbstractMailEventListener
{
    public function onPreSend(MailEvent $e): void
    {
        $e->getMailService()->getMessage()->addBcc('audit@example.com');
    }

    public function onPostSend(MailEvent $e): void
    {
        // the message was delivered to the transport
    }

    public function onSendError(MailEvent $e): void
    {
        error_log((string) $e->getResult()?->getMessage());
    }
}
```

- `onPreSend` runs before the attachments are added and the message is sent. `getResult()` returns `null`.
- `onPostSend` runs after a successful send, before the message is reset. An exception thrown here reaches the caller of `send()` unchanged: the message was already delivered.
- `onSendError` runs when sending fails. `getResult()->getException()` returns the original exception.

Register listeners in the `event_listeners` configuration key, or call `attachListener()` on the service.
`MailEvent` extends `Laminas\EventManager\Event`.

## Logging outgoing emails

When `dot_mail.log.sent` is set, `Dot\Mail\Service\LogService` appends one line per sent email to that file:

```text
[YYYY-MM-DD HH:MM:SS]: {"subject":"Test subject","to":["Test Account <test@dotkernel.com>"],"cc":[],"bcc":[]}
```

A failure to write the log does not turn a delivered email into an error.

## Transport usage

See [Transports](transports.md).
