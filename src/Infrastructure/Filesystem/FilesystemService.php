<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use HexagonalPlayground\Domain\Exception\InternalException;
use Iterator;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

class FilesystemService
{
    /**
     * Create a directory (non-recursive)
     *
     * @param string $path
     * @return void
     * @throws InternalException
     */
    public function createDirectory(string $path): void
    {
        if (false === mkdir($path)) {
            throw new InternalException("Failed to create directory $path");
        }
    }

    /**
     * Create a file (directory must exist)
     *
     * @param string $path
     * @param string $data
     * @return void
     * @throws InternalException
     */
    public function createFile(string $path, string $data = ''): void
    {
        if (false === file_put_contents($path, $data)) {
            throw new InternalException("Failed to create file $path");
        }
    }

    /**
     * Delete a directory (must be empty)
     *
     * @param string $path
     * @return void
     * @throws InternalException
     */
    public function deleteDirectory(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (!is_dir($path)) {
            throw new InternalException("Failed to delete $path: Not a directory");
        }

        if (false === rmdir($path)) {
            throw new InternalException("Failed to delete directory $path");
        }
    }

    /**
     * Delete a file
     *
     * @param string $path
     * @return void
     * @throws InternalException
     */
    public function deleteFile(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (!is_file($path)) {
            throw new InternalException("Failed to delete $path: Not a file");
        }

        if (false === unlink($path)) {
            throw new InternalException("Failed to delete file $path");
        }
    }

    /**
     * List contents of a directory
     *
     * @param string $path
     * @return array
     * @throws InternalException
     */
    public function getDirectoryContents(string $path): array
    {
        return iterator_to_array($this->getDirectoryIterator($path));
    }

    /**
     * Create an Iterator representing a directory's content
     *
     * @param string $path
     * @return Iterator
     * @throws InternalException
     */
    public function getDirectoryIterator(string $path): Iterator
    {
        $handle = opendir($path);
        if (!is_resource($handle)) {
            throw new InternalException("Failed to open directory $path");
        }

        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                yield $entry;
            }
        }
        closedir($handle);
    }

    /**
     * Read a file entirely into memory
     *
     * @param string $path
     * @return string
     * @throws InternalException
     */
    public function getFileContents(string $path): string
    {
        $data = file_get_contents($path);
        if ($data === false) {
            throw new InternalException("Failed to read file $path");
        }

        return $data;
    }

    /**
     * Returns absolute path (resolving symlinks and dots)
     *
     * @param string $path
     * @return string
     */
    public function getRealPath(string $path): string
    {
        return realpath($path);
    }

    /**
     * Determines if there is a directory at the specified path
     *
     * @param string $path
     * @return bool
     */
    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Determine if there is a file at the specified path
     *
     * @param string $path
     * @return bool
     */
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    /**
     * Determines if the file/directory at the specified path is writable
     *
     * @param string $path
     * @return bool
     */
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * Joins multiple path segment into a single path (not resolving symlink or dots)
     *
     * @param array $paths
     * @return string
     */
    public function joinPaths(array $paths): string
    {
        return join(DIRECTORY_SEPARATOR, $paths);
    }

    /**
     * Opens a file and returns a stream
     *
     * @param string $path
     * @param string $mode
     * @return StreamInterface
     * @throws InternalException
     */
    public function openFile(string $path, string $mode): StreamInterface
    {
        $resource = fopen($path, $mode);
        if (!is_resource($resource)) {
            throw new InternalException("Failed to open file $path in mode '$mode'");
        }

        return new Stream($resource);
    }
}
