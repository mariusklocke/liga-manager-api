<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use HexagonalPlayground\Domain\Exception\InternalException;
use Psr\Http\Message\StreamInterface;

class FileStream implements StreamInterface
{
    /** @var resource */
    private $stream;

    /** @var int|null */
    private ?int $size;

    /**
     * @param string $path
     * @param string $mode
     */
    public function __construct(string $path, string $mode = 'r')
    {
        $this->stream = fopen($path, $mode);
        $this->assertThat(is_resource($this->stream), 'Failed to open file %s in mode "%s"');
        $size = filesize($path);
        $this->size = is_int($size) ? $size : null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $this->rewind();

        return $this->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        $position = ftell($this->stream);

        $this->assertThat(is_int($position), 'Failed getting current position of FileStream.');

        return $position;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return feof($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return (bool) $this->getMetadata('seekable');
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        $code = fseek($this->stream, $offset, $whence);
        $this->assertThat($code !== -1, sprintf('Failed to seek to position %d in stream.', $offset));
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        $mode = $this->getMetadata('mode');
        if (!is_string($mode)) {
            return false;
        }

        return $mode !== 'r';
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        $written = fwrite($this->stream, $string);

        $this->assertThat(is_int($written), 'Failed writing to stream.');

        $this->size += $written;

        return $written;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        $mode = $this->getMetadata('mode');
        if (!is_string($mode)) {
            return false;
        }

        return (false !== strpos($mode, 'r') || false !== strpos($mode, '+'));
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $read = fread($this->stream, $length);

        $this->assertThat(is_string($read), 'Failed reading from stream.');

        return $read;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        $data = '';
        while (!$this->eof()) {
            $data .= $this->read(2048);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        $metadata = stream_get_meta_data($this->stream);
        if ($key === null) {
            return $metadata;
        }

        return $metadata[$key] ?? null;
    }

    private function assertThat(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new InternalException($message);
        }
    }
}
