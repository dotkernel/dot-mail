# dot-mail

DotKernel mail component based on [laminas-mail](https://github.com/laminas/laminas-mail)


![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/dot-mail)
![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-mail/3.4.0)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/blob/3.0/LICENSE.md)



### Testing if an e-mail message is valid
After sending an e-mail you can check if the message was valid or not.
The `$this->mailService->send()->isValid()` method call will return a boolean value.
If the returned result is `true`, the e-mail was valid, otherwise the e-mail was invalid.
In case your e-mail was invalid, you can check for any errors using `$this->mailService->send()->getMessage()`.

Using the below logic will let you determine if a message was valid or not and log it.
You can implement your own custom error logging logic.

````
$result = $this->mailService->send();
if (! $result->isValid()) {
    //log the error
    error_log($result->getMessage());
}
````
**Note : Invalid e-mail messages will not be sent.**


### Logging outgoing emails
Optionally, you can keep a log of each successfully sent email. This might be useful when you need to know if/when a specific email has been sent out to a recipient.

Logs are stored in the following format: `[YYYY-MM-DD HH:MM:SS]: {"subject":"Test subject","to":["Test Account <test@dotkernel.com>"],"cc":[],"bcc":[]}`.

By default, this feature is disabled.

In order to enable it, make sure that your `config/autoload/mail.local.php` has the below `log` configuration under the `dot_mail` key:
```php
<?php

return [
    'dot_mail' => [
        ...
        'log' => [
            'sent' => getcwd() . '/log/mail/sent.log'
        ]
    ]
];
```
To disable it again, set the value of `sent` to `null`.


### Saving a copy of an outgoing mail into a folder
First, make sure the `save_sent_message_folder` key is present in config file `mail.local.php` under `dot_mail.default`. Below you can see its placement and default value. 
```
return [
    'dot_mail' => [
        'default' => [
        ...
            'save_sent_message_folder' => ['INBOX.Sent']
        ],
    ],
],
```
Common folder names are `INBOX`, `INBOX.Archive`, `INBOX.Drafts`, `INBOX.Sent`, `INBOX.Spam`, `INBOX.Trash`. If you have `MailService` available in your class, you can call `$this->mailService->getFolderGlobalNames()` to list the folder global names for the email you are using.

Multiple folders can be added to the `save_sent_message_folder` key to save a copy of the outgoing email in each folder.