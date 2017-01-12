<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace Dot\Mail\Service;

use Dot\Helpers\Psr7\HttpMessagesAwareInterface;
use Dot\Mail\Result\ResultInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime\Part;

/**
 * Interface MailServiceInterface
 * @package Dot\Mail\Service
 */
interface MailServiceInterface extends HttpMessagesAwareInterface
{
    const DEFAULT_CHARSET = 'utf-8';

    /**
     * @return ResultInterface
     */
    public function send();

    /**
     * @return Message
     */
    public function getMessage();

    /**
     * @return TransportInterface
     */
    public function getTransport();

    /**
     * @param string|Part|\Zend\Mime\Message $body
     * @param string $charset
     * @return mixed
     */
    public function setBody($body, $charset = null);

    /**
     * @param string $template
     * @param array $params
     * @return mixed
     */
    public function setTemplate($template, array $params = []);

    /**
     * @param string $subject
     * @return mixed
     */
    public function setSubject($subject);

    /**
     * @param string $path
     * @param string|null $filename
     * @return mixed
     */
    public function addAttachment($path, $filename = null);

    /**
     * @param array $paths
     * @return mixed
     */
    public function addAttachments(array $paths);

    /**
     * @return array
     */
    public function getAttachments();

    /**
     * @param array $paths
     * @return mixed
     */
    public function setAttachments(array $paths);
}
