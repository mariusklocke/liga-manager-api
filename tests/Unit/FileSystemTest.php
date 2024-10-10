<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class FileSystemTest extends TestCase
{
    private FilesystemService $service;

    protected function setUp(): void
    {
        $streamFactory = new Psr17Factory();
        $this->service = new FilesystemService($streamFactory);
    }

    public function testBasicFileOperations(): void
    {
        self::assertTrue($this->service->isDirectory(sys_get_temp_dir()));

        $basePath = $this->service->joinPaths([sys_get_temp_dir(), uniqid('phpunit')]);
        $this->service->createDirectory($basePath);
        self::assertTrue($this->service->isDirectory($basePath));
        self::assertCount(0, $this->service->getDirectoryContents($basePath));

        $filePath = $this->service->joinPaths([$basePath, uniqid('phpunit')]);
        $fileContent = uniqid();
        $this->service->createFile($filePath, $fileContent);
        self::assertSame($fileContent, $this->service->getFileContents($filePath));
        self::assertFalse($this->service->isDirectory($filePath));
        self::assertCount(1, $this->service->getDirectoryContents($basePath));

        $this->service->deleteFile($filePath);
        self::assertCount(0, $this->service->getDirectoryContents($basePath));

        $this->service->deleteDirectory($basePath);
        self::assertFalse($this->service->isDirectory($basePath));
    }

    public function testFileCanBeWritten(): string
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), uniqid('phpunit'));
        $stream = $this->service->openFile($tempFilePath, 'w');
        self::assertFalse($stream->isReadable());
        self::assertTrue($stream->isWritable());
        self::assertSame(0, $stream->getSize());
        self::assertSame(\strlen(__CLASS__), $stream->write(__CLASS__));
        self::assertSame(\strlen(__CLASS__), $stream->tell());
        self::assertSame(\strlen(__CLASS__), $stream->getSize());
        $stream->close();

        return $tempFilePath;
    }

    /**
     * @param string $tempFilePath
     */
    #[Depends("testFileCanBeWritten")]
    public function testFileCanBeRead(string $tempFilePath): void
    {
        $stream = $this->service->openFile($tempFilePath, 'r');
        self::assertFalse($stream->isWritable());
        self::assertTrue($stream->isReadable());
        self::assertTrue($stream->isSeekable());
        self::assertFalse($stream->eof());
        self::assertSame(\strlen(__CLASS__), $stream->getSize());
        self::assertSame(__CLASS__, (string) $stream);
        self::assertTrue($stream->eof());
        self::assertSame(\strlen(__CLASS__), $stream->tell());
        $stream->close();
    }
}
