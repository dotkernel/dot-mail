# dot-mail

DotKernel mail component based on [laminas-mail](https://github.com/laminas/laminas-mail)


### Testing if an e-mail message is valid
After sending an e-mail you can check if the message was valid or not.
The `$this->mailService->send()->isValid()` method call will return a boolean value.
If the returned result is `true`, the e-mail was valid, otherwise the e-mail was invalid.
In case your e-mail was invalid, you can check for any errors using `$this->mailService->send()->getMessage()`.

Using the below logic will let you determinate if a message was valid or not and log it.

The implementor can write it's own custom error log logic.

````
$result = $this->mailService->send();
if (! $result->isValid()) {
    //log the error
    error_log($result->getMessage());
}
````
**Note : Invalid e-mail messages will not be sent.**


![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/dot-mail)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/issues)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/dot-mail)](https://github.com/dotkernel/dot-mail/blob/3.0/LICENSE.md)
