<?php
/**
 * @see https://github.com/dotkernel/dot-mail/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-mail/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Mail\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class AttachmentsOptions
 * @package Dot\Mail\Options
 */
class AttachmentsOptions extends AbstractOptions
{
    const DEFAULT_ITERATE = false;
    const DEFAULT_PATH = 'data/mail/attachments';
    const DEFAULT_RECURSIVE = false;

    /** @var array */
    protected $files = [];

    /** @var array */
    protected $dir = [
        'iterate' => self::DEFAULT_ITERATE,
        'path' => self::DEFAULT_PATH,
        'recursive' => self::DEFAULT_RECURSIVE,
    ];

    /**
     * @return array
     */
    public function getDir(): array
    {
        return $this->dir;
    }

    /**
     * @param array $dir
     */
    public function setDir(array $dir)
    {
        $this->dir = $dir;
        $this->normalizeDirArray();
    }

    /**
     * Makes sure dir array has default properties at least
     */
    protected function normalizeDirArray()
    {
        if (!isset($this->dir['iterate'])) {
            $this->dir['iterate'] = self::DEFAULT_ITERATE;
        }
        if (!isset($this->dir['path'])) {
            $this->dir['path'] = self::DEFAULT_PATH;
        }
        if (!isset($this->dir['recursive'])) {
            $this->dir['recursive'] = self::DEFAULT_RECURSIVE;
        }
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param array $files
     */
    public function setFiles(array $files)
    {
        $this->files = $files;
    }

    /**
     * @param string $filePath
     */
    public function addFile(string $filePath)
    {
        $this->files[] = $filePath;
    }

    /**
     * @param array $files
     */
    public function addFiles(array $files)
    {
        $this->setFiles(array_merge($this->files, $files));
    }
}
