# Usage

## Sending an e-mail

Below is an example of how to use the email in the most basic way. You can add your own code to it e.g. to get the user data from a User object or from a config file, to use a template for the body.

Note that `addTo` is only one of the methods available for the `Message` class returned by `getMessage()`. Other useful methods that were not included in the example are `addCc()`, `addBcc()`, `addReplyTo()`.

The returned type is boolean, but if the `isValid()` method is removed, the returned type becomes `MailResult` which allows the use of `getMessage()` for a more detailed error message. See the `Testing if an e-mail message is valid` section below.

```php
public function sendBasicMail()
{
    $this->mailService->setBody('Email body');
    $this->mailService->setSubject('Email subject');
    $this->mailService->getMessage()->addTo('email@example.com', 'User name');
    $this->mailService->getMessage()->setEncoding('utf-8');
    return $this->mailService->send()->isValid();
}
```

It's optional, but recommended to call the above function in a `try-catch` block to display helpful error messages. The next example calls the `sendBasicMail` function from within `UserController`, but you can implement it in other controllers, just make sure that the controller's construct also includes the `FlashMessenger` parameter `$messenger`.

```php
try {
    $this->userService->sendBasicMail();
    $this->messenger->addSuccess('The mail was sent successfully', 'user-login');
    //more code...
} catch (Exception $exception) {
    $this->messenger->addError($exception->getMessage(), 'user-login');
    //more code...
}
```

## Testing if an e-mail message is valid

After sending an e-mail you can check if the message was valid or not.
The `$this->mailService->send()->isValid()` method call will return a boolean value.
If the returned result is `true`, the e-mail was valid, otherwise the e-mail was invalid.
In case your e-mail was invalid, you can check for any errors using `$this->mailService->send()->getMessage()`.

Using the below logic will let you determine if a message was valid or not and log it.
You can implement your own custom error logging logic.

```php
$result = $this->mailService->send();
if (! $result->isValid()) {
    //log the error
    error_log($result->getMessage());
}
```

**Note : Invalid e-mail messages will not be sent.**

## Logging outgoing emails

Optionally, you can keep a log of each successfully sent email. This might be useful when you need to know if/when a specific email has been sent out to a recipient.

Logs are stored in the following format: `[YYYY-MM-DD HH:MM:SS]: {"subject":"Test subject","to":["Test Account <test@dotkernel.com>"],"cc":[],"bcc":[]}`.

Each email is saved via the `dotkernel/dot-log` component in the shown `JSON` format, in the single file configured under the `dot-mail.log.sent` key. To disable it, set the value of `dot-mail.log.sent` to `null`.

## Transport usage

[Transports](transports.md)
