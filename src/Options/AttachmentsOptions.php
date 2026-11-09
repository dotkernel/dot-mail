<?php

declare(strict_types=1);

namespace Dot\Mail\Options;

use Laminas\Stdlib\AbstractOptions;

use function array_merge;

/**
 * @extends AbstractOptions<mixed>
 */
class AttachmentsOptions extends AbstractOptions
{
    public const DEFAULT_ITERATE   = false;
    public const DEFAULT_PATH      = 'data/mail/attachments';
    public const DEFAULT_RECURSIVE = false;

    /** @var array<int|string, string> */
    protected array $files = [];
    /** @var array{iterate: bool, path: string, recursive: bool} */
    protected array $dir = [
        'iterate'   => self::DEFAULT_ITERATE,
        'path'      => self::DEFAULT_PATH,
        'recursive' => self::DEFAULT_RECURSIVE,
    ];

    /**
     * @return array{iterate: bool, path: string, recursive: bool}
     */
    public function getDir(): array
    {
        return $this->dir;
    }

    /**
     * @param array{iterate?: bool, path?: string, recursive?: bool} $dir
     */
    public function setDir(array $dir): void
    {
        $this->dir = [
            'iterate'   => $dir['iterate'] ?? self::DEFAULT_ITERATE,
            'path'      => $dir['path'] ?? self::DEFAULT_PATH,
            'recursive' => $dir['recursive'] ?? self::DEFAULT_RECURSIVE,
        ];
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

    /**
     * @return array<int|string, string>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param array<int|string, string> $files
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    public function addFile(string $filePath): void
    {
        $this->files[] = $filePath;
    }

    /**
     * @param array<int|string, string> $files
     */
    public function addFiles(array $files): void
    {
        $this->setFiles(array_merge($this->files, $files));
    }
}
