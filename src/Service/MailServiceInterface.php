<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

declare(strict_types = 1);

namespace Dot\Mail\Service;

use Dot\Helpers\Psr7\HttpMessagesAwareInterface;
use Dot\Mail\Result\ResultInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;

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
    public function send(): ResultInterface;

    /**
     * @return Message
     */
    public function getMessage(): Message;

    /**
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface;

    /**
     * @param mixed $body
     * @param string $charset
     */
    public function setBody($body, string $charset = null);

    /**
     * @param string $template
     * @param array $params
     * @param string|null $charset
     */
    public function setTemplate(string $template, array $params = [], string $charset = null);

    /**
     * @param string $subject
     */
    public function setSubject(string $subject);

    /**
     * @param string $path
     * @param string|null $filename
     */
    public function addAttachment(string $path, string $filename = null);

    /**
     * @param array $paths
     */
    public function addAttachments(array $paths);

    /**
     * @return array
     */
    public function getAttachments(): array;

    /**
     * @param array $paths
     */
    public function setAttachments(array $paths);
}
