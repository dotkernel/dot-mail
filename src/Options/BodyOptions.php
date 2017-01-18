<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-mail
 * @author: n3vrax
 * Date: 9/6/2016
 * Time: 7:49 PM
 */

namespace Dot\Mail\Options;

use Dot\Mail\Exception\InvalidArgumentException;
use Dot\Mail\Service\MailServiceInterface;
use Zend\Stdlib\AbstractOptions;

/**
 * Class BodyOptions
 * @package Dot\Mail\Options
 */
class BodyOptions extends AbstractOptions
{
    /** @var bool */
    protected $useTemplate = false;

    /** @var string */
    protected $content = '';

    /** @var string */
    protected $charset = MailServiceInterface::DEFAULT_CHARSET;

    /** @var  TemplateOptions */
    protected $template;

    /**
     * @return boolean
     */
    public function isUseTemplate()
    {
        return $this->useTemplate;
    }

    /**
     * @param boolean $useTemplate
     * @return BodyOptions
     */
    public function setUseTemplate($useTemplate)
    {
        $this->useTemplate = (bool)$useTemplate;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return BodyOptions
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return BodyOptions
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * @return TemplateOptions
     */
    public function getTemplate()
    {
        if (!isset($this->template)) {
            $this->setTemplate([]);
        }
        return $this->template;
    }

    /**
     * @param TemplateOptions|array $template
     * @return BodyOptions
     */
    public function setTemplate($template)
    {
        if (is_array($template)) {
            $this->template = new TemplateOptions($template);
        } elseif ($template instanceof TemplateOptions) {
            $this->template = $template;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Template should be an array or an %s object. %s provided.',
                TemplateOptions::class,
                is_object($template) ? get_class($template) : gettype($template)
            ));
        }
        return $this;
    }
}
