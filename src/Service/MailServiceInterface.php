<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Service;

use Dot\Mail\Result\ResultInterface;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;

/**
 * Interface MailServiceInterface
 * @package Dot\Mail\Service
 */
interface MailServiceInterface
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
