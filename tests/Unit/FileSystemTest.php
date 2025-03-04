<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use HexagonalPlayground\Tests\Framework\File;
use Nyholm\Psr7\Factory\Psr17Factory;
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
        $tempFile = File::temp('filesystem_test_', '.tmp');
        $tempDir  = dirname($tempFile->getPath());

        self::assertTrue($this->service->isDirectory($tempDir));

        $basePath = $this->service->joinPaths([$tempDir, uniqid('phpunit')]);
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

    public function testFileCanBeWritten(): void
    {
        $tempFile = File::temp('filesystem_test_', '.tmp');
        try {
            $stream = $this->service->openFile($tempFile->getPath(), 'w');
            self::assertFalse($stream->isReadable());
            self::assertTrue($stream->isWritable());
            self::assertSame(0, $stream->getSize());
            self::assertSame(\strlen(__CLASS__), $stream->write(__CLASS__));
            self::assertSame(\strlen(__CLASS__), $stream->tell());
            self::assertSame(\strlen(__CLASS__), $stream->getSize());
            $stream->close();
        } finally {
            $tempFile->delete();
        }
    }

    public function testFileCanBeRead(): void
    {
        $tempFile = File::temp('filesystem_test_', '.tmp');
        $tempFile->write(__CLASS__);
        try {
            $stream = $this->service->openFile($tempFile->getPath(), 'r');
            self::assertFalse($stream->isWritable());
            self::assertTrue($stream->isReadable());
            self::assertTrue($stream->isSeekable());
            self::assertFalse($stream->eof());
            self::assertSame(\strlen(__CLASS__), $stream->getSize());
            self::assertSame(__CLASS__, (string) $stream);
            self::assertTrue($stream->eof());
            self::assertSame(\strlen(__CLASS__), $stream->tell());
            $stream->close();
        } finally {
            $tempFile->delete();
        }
    }
}
