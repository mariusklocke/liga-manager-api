<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use RuntimeException;

trait PathTrait
{
    private string $path;

    /**
     * @param string ...$parts
     */
    public function __construct(string ...$parts)
    {
        $this->path = join(DIRECTORY_SEPARATOR, $parts);
        $this->assertTrue($this->path !== '', 'Cannot construct file or directory: Empty path');
    }

    /**
     * Returns if the path exists
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->path);
    }

    /**
     * Returns the basename of the path
     * 
     * @return string
     */
    public function getName(): string
    {
        return basename($this->path);
    }

    /**
     * Returns the path
     * 
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the absolute path (resolves symlinks)
     * 
     * @return string
     */
    public function getRealpath(): string
    {
        return realpath($this->path);
    }

    /**
     * Returns if the path is readable
     * 
     * @return bool
     */
    public function isReadable(): bool
    {
        return is_readable($this->path);
    }

    /**
     * Returns if the path is writable
     * 
     * @return bool
     */
    public function isWritable(): bool
    {
        return is_writable($this->path);
    }

    /**
     * Asserts that a value is true
     * 
     * @param bool $value
     * @param string $message
     */
    private function assertTrue(bool $value, string $message): void
    {
        if (!$value) {
            throw new RuntimeException($message);
        }
    }
}