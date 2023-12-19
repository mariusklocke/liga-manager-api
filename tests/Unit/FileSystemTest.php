<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
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
}
