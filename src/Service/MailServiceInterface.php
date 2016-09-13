<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace DotKernel\DotMail\Service;

use DotKernel\DotMail\Result\ResultInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Mail\Message;
use Zend\Mime\Part;

/**
 * Interface MailServiceInterface
 * @package DotKernel\DotMail\Service
 */
interface MailServiceInterface
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

    /**
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function setRequest(ServerRequestInterface $request);

    /**
     * @return mixed
     */
    public function getRequest();

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    public function setResponse(ResponseInterface $response);

    /**
     * @return mixed
     */
    public function getResponse();
}