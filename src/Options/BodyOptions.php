<?php

declare(strict_types=1);

namespace Dot\Mail\Options;

use Dot\Mail\Service\MailServiceInterface;
use Laminas\Stdlib\AbstractOptions;

/**
 * @template TValue
 * @template-extends AbstractOptions<TValue>
 */
class BodyOptions extends AbstractOptions
{
    protected string $content = '';
    protected string $charset = MailServiceInterface::DEFAULT_CHARSET;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }
}
