<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace Dot\Mail\Options;

use Dot\Mail\Event\MailListenerInterface;
use Dot\Mail\Exception\InvalidArgumentException;
use Zend\Mail\Transport\File;
use Zend\Mail\Transport\FileOptions;
use Zend\Mail\Transport\InMemory;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\TransportInterface;
use Zend\Stdlib\AbstractOptions;

/**
 * Class MailOptions
 * @package Dot\Mail\Options
 */
class MailOptions extends AbstractOptions
{
    protected $transportMap = [
        'sendmail' => [Sendmail::class],
        'smtp' => [Smtp::class],
        'in_memory' => [InMemory::class],
        'file' => [File::class],
    ];

    /** @var  TransportInterface|string */
    protected $transport = Sendmail::class;

    /** @var  MessageOptions */
    protected $messageOptions;

    /** @var  SmtpOptions */
    protected $smtpOptions;

    /** @var  FileOptions */
    protected $fileOptions;

    /** @var MailListenerInterface[] */
    protected $mailListeners = [];

    /**
     * @return array
     */
    public function getTransportMap()
    {
        return $this->transportMap;
    }

    /**
     * @param array $transportMap
     * @return MailOptions
     */
    public function setTransportMap($transportMap)
    {
        $this->transportMap = $transportMap;
        return $this;
    }

    /**
     * @return string|TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param string|TransportInterface $transport
     * @return MailOptions
     */
    public function setTransport($transport)
    {
        if (is_string($transport) && array_key_exists(strtolower($transport), $this->transportMap)) {
            $transport = $this->transportMap[$transport];
            foreach ($transport as $class) {
                if (class_exists($class)) {
                    $transport = $class;
                    break;
                }
            }
        }

        $this->transport = $transport;
        return $this;
    }

    /**
     * @return MessageOptions
     */
    public function getMessageOptions()
    {
        if (!isset($this->messageOptions)) {
            $this->setMessageOptions([]);
        }

        return $this->messageOptions;
    }

    /**
     * @param MessageOptions|array $messageOptions
     * @return MailOptions
     */
    public function setMessageOptions($messageOptions)
    {
        if (is_array($messageOptions)) {
            $this->messageOptions = new MessageOptions($messageOptions);
        } elseif ($messageOptions instanceof MessageOptions) {
            $this->messageOptions = $messageOptions;
        } else {
            throw new InvalidArgumentException(sprintf(
                'MessageOptions should be an array or an %s object. %s provided',
                MessageOptions::class,
                is_object($messageOptions) ? get_class($messageOptions) : gettype($messageOptions)
            ));
        }

        return $this;
    }

    /**
     * @return SmtpOptions
     */
    public function getSmtpOptions()
    {
        if (!isset($this->smtpOptions)) {
            $this->setSmtpOptions([]);
        }

        return $this->smtpOptions;
    }

    /**
     * @param SmtpOptions|array $smtpOptions
     * @return MailOptions
     */
    public function setSmtpOptions($smtpOptions)
    {
        if (is_array($smtpOptions)) {
            $this->smtpOptions = new SmtpOptions($smtpOptions);
        } elseif ($smtpOptions instanceof SmtpOptions) {
            $this->smtpOptions = $smtpOptions;
        } else {
            throw new InvalidArgumentException(sprintf(
                'SmtpOptions should be an array or an %s object. %s provided.',
                SmtpOptions::class,
                is_object($smtpOptions) ? get_class($smtpOptions) : gettype($smtpOptions)
            ));
        }

        return $this;
    }

    /**
     * @return FileOptions
     */
    public function getFileOptions()
    {
        if (!isset($this->fileOptions)) {
            $this->setFileOptions([]);
        }

        return $this->fileOptions;
    }

    /**
     * @param FileOptions|array $fileOptions
     * @return MailOptions
     */
    public function setFileOptions($fileOptions)
    {
        if (is_array($fileOptions)) {
            $this->fileOptions = new FileOptions($fileOptions);
        } elseif ($fileOptions instanceof FileOptions) {
            $this->fileOptions = $fileOptions;
        } else {
            throw new InvalidArgumentException(sprintf(
                'FileOptions should be an array or an %s object. %s provided.',
                FileOptions::class,
                is_object($fileOptions) ? get_class($fileOptions) : gettype($fileOptions)
            ));
        }

        return $this;
    }

    /**
     * @return \Dot\Mail\Event\MailListenerInterface[]
     */
    public function getMailListeners()
    {
        return $this->mailListeners;
    }

    /**
     * @param \Dot\Mail\Event\MailListenerInterface[] $mailListeners
     * @return MailOptions
     */
    public function setMailListeners($mailListeners)
    {
        $this->mailListeners = (array)$mailListeners;
        return $this;
    }
}
