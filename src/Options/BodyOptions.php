<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Options;

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
     * @return bool
     */
    public function isUseTemplate(): bool
    {
        return $this->useTemplate;
    }

    /**
     * @param bool $useTemplate
     */
    public function setUseTemplate(bool $useTemplate)
    {
        $this->useTemplate = $useTemplate;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset(string $charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return TemplateOptions
     */
    public function getTemplate(): TemplateOptions
    {
        if (!isset($this->template)) {
            $this->setTemplate([]);
        }
        return $this->template;
    }

    /**
     * @param array $template
     */
    public function setTemplate(array $template)
    {
        $this->template = new TemplateOptions($template);
    }
}
