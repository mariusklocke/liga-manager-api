<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class FileSystemTest extends TestCase
{
    public function testBasicFileOperations(): void
    {
        $service = new FilesystemService();
        self::assertTrue($service->isDirectory(sys_get_temp_dir()));

        $basePath = $service->joinPaths([sys_get_temp_dir(), uniqid('phpunit')]);
        $service->createDirectory($basePath);
        self::assertTrue($service->isDirectory($basePath));
        self::assertCount(0, $service->getDirectoryContents($basePath));

        $filePath = $service->joinPaths([$basePath, uniqid('phpunit')]);
        $fileContent = uniqid();
        $service->createFile($filePath, $fileContent);
        self::assertSame($fileContent, $service->getFileContents($filePath));
        self::assertFalse($service->isDirectory($filePath));
        self::assertCount(1, $service->getDirectoryContents($basePath));

        $service->deleteFile($filePath);
        self::assertCount(0, $service->getDirectoryContents($basePath));

        $service->deleteDirectory($basePath);
        self::assertFalse($service->isDirectory($basePath));
    }

    public function testFileCanBeWritten(): string
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), uniqid('phpunit'));
        $service = new FilesystemService();
        $stream = $service->openFile($tempFilePath, 'w');
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
        $service = new FilesystemService();
        $stream = $service->openFile($tempFilePath, 'r');
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
