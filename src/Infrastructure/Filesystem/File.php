<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class File
{
    use PathTrait;

    private static ?StreamFactoryInterface $streamFactory = null;
    
    /**
     * Clear file contents
     */
    public function clear(): void
    {
        $this->write('');
    }

    /**
     * Delete file
     */
    public function delete(): void
    {
        if ($this->exists()) {
            $this->assertTrue($this->isWritable(), "Cannot delete {$this->path}: File is not writable");
            unlink($this->path);
        }
    }

    /**
     * Returns the size of the file
     * 
     * @return int bytes
     */
    public function getSize(): int
    {
        return filesize($this->path);
    }

    /**
     * Returns if file is empty
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->getSize() === 0;
    }

    /**
     * Returns a stream for the file
     * 
     * @param string $mode
     *
     * @return StreamInterface
     */
    public function open(string $mode): StreamInterface
    {
        return self::getStreamFactory()->createStreamFromFile($this->path, $mode);
    }

    /**
     * Returns the content of the file
     * 
     * @return string
     */
    public function read(): string
    {
        $this->assertTrue($this->exists(), "Cannot read {$this->path}: File does not exist");
        $this->assertTrue($this->isReadable(), "Cannot read {$this->path}: File is not readable");

        return file_get_contents($this->path);
    }

    /**
     * Write data to the file
     * 
     * @param string $data
     */
    public function write(string $data): void
    {
        $this->assertTrue(!$this->exists() || $this->isWritable(), "Cannot write to {$this->path}: File is not writable");
        file_put_contents($this->path, $data);
    }

    /**
     * Returns the stream factory
     * 
     * @return StreamFactoryInterface
     */
    private static function getStreamFactory(): StreamFactoryInterface
    {
        if (self::$streamFactory === null) {
            self::$streamFactory = new Psr17Factory();
        }

        return self::$streamFactory;
    }
}