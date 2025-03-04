<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use RuntimeException;

class File
{
    private string $path;

    public function __construct(string $directory, string $filename)
    {
        if (!is_dir($directory)) {
            throw new RuntimeException('Directory does not exist: ' . $directory);
        }
        $this->path = join(DIRECTORY_SEPARATOR, [$directory, $filename]);
    }

    public function delete(): void
    {
        if ($this->exists()) {
            unlink($this->path);
        }
    }

    public function empty(): void
    {
        $this->write('');
    }

    public function exists(): bool
    {
        return file_exists($this->path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSize(): int
    {
        return filesize($this->path);
    }

    public function read(): string
    {
        return file_get_contents($this->path);
    }

    public function write(string $content): void
    {
        file_put_contents($this->path, $content);
    }

    public static function temp(string $prefix = '', string $extension = ''): self
    {
        return new self(sys_get_temp_dir(), uniqid($prefix, true) . $extension);
    }
}