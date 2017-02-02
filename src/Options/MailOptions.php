<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

declare(strict_types = 1);

namespace Dot\Mail\Options;

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
    /** @var array */
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

    /** @var array */
    protected $eventListeners = [];

    /**
     * @return array
     */
    public function getTransportMap(): array
    {
        return $this->transportMap;
    }

    /**
     * @param array $transportMap
     */
    public function setTransportMap(array $transportMap)
    {
        $this->transportMap = $transportMap;
    }

    /**
     * @return string
     */
    public function getTransport(): string
    {
        return $this->transport;
    }

    /**
     * @param string $transport
     */
    public function setTransport(string $transport)
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
    }

    /**
     * @return MessageOptions
     */
    public function getMessageOptions(): MessageOptions
    {
        if (!isset($this->messageOptions)) {
            $this->setMessageOptions([]);
        }

        return $this->messageOptions;
    }

    /**
     * @param array $messageOptions
     */
    public function setMessageOptions(array $messageOptions)
    {
        $this->messageOptions = new MessageOptions($messageOptions);
    }

    /**
     * @return SmtpOptions
     */
    public function getSmtpOptions(): SmtpOptions
    {
        if (!isset($this->smtpOptions)) {
            $this->setSmtpOptions([]);
        }

        return $this->smtpOptions;
    }

    /**
     * @param array $smtpOptions
     */
    public function setSmtpOptions(array $smtpOptions)
    {
        $this->smtpOptions = new SmtpOptions($smtpOptions);
    }

    /**
     * @return FileOptions
     */
    public function getFileOptions(): FileOptions
    {
        if (!isset($this->fileOptions)) {
            $this->setFileOptions([]);
        }

        return $this->fileOptions;
    }

    /**
     * @param array $fileOptions
     */
    public function setFileOptions(array $fileOptions)
    {
        $this->fileOptions = new FileOptions($fileOptions);
    }

    /**
     * @return array
     */
    public function getEventListeners(): array
    {
        return $this->eventListeners;
    }

    /**
     * @param array $eventListeners
     */
    public function setEventListeners(array $eventListeners)
    {
        $this->eventListeners = $eventListeners;
    }
}
