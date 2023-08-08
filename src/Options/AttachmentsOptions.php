<?php

declare(strict_types=1);

namespace Dot\Mail\Options;

use Laminas\Stdlib\AbstractOptions;

use function array_merge;

class AttachmentsOptions extends AbstractOptions
{
    public const DEFAULT_ITERATE   = false;
    public const DEFAULT_PATH      = 'data/mail/attachments';
    public const DEFAULT_RECURSIVE = false;

    protected array $files = [];
    protected array $dir   = [
        'iterate'   => self::DEFAULT_ITERATE,
        'path'      => self::DEFAULT_PATH,
        'recursive' => self::DEFAULT_RECURSIVE,
    ];

    public function getDir(): array
    {
        return $this->dir;
    }

    public function setDir(array $dir): void
    {
        $this->dir = $dir;
        $this->normalizeDirArray();
    }

    /**
     * Makes sure dir array has default properties at least
     */
    protected function normalizeDirArray(): void
    {
        if (! isset($this->dir['iterate'])) {
            $this->dir['iterate'] = self::DEFAULT_ITERATE;
        }
        if (! isset($this->dir['path'])) {
            $this->dir['path'] = self::DEFAULT_PATH;
        }
        if (! isset($this->dir['recursive'])) {
            $this->dir['recursive'] = self::DEFAULT_RECURSIVE;
        }
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    public function addFile(string $filePath): void
    {
        $this->files[] = $filePath;
    }

    public function addFiles(array $files): void
    {
        $this->setFiles(array_merge($this->files, $files));
    }
}
