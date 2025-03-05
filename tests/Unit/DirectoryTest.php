<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\Filesystem\Directory;
use HexagonalPlayground\Infrastructure\Filesystem\File;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    public function testCanBeRead(): void
    {
        $directory = new Directory(__DIR__);
        self::assertTrue($directory->exists());
        self::assertSame('Unit', $directory->getName());
        self::assertSame(__DIR__, $directory->getPath());
        self::assertFalse($directory->isEmpty());
        $items = [];
        foreach ($directory->list() as $item) {
            $items[] = $item->getName();
        }
        self::assertContains('DirectoryTest.php', $items);
        self::assertNotContains('TeamTest.php', $items);
    }

    public function testCanBeWritten(): void
    {
        $tempDir = Directory::getTemp();
        self::assertTrue($tempDir->exists());
        self::assertSame(sys_get_temp_dir(), $tempDir->getPath());

        $subDir = new Directory($tempDir->getPath(), uniqid('dir_test_', true));
        self::assertFalse($subDir->exists());
        $subDir->create();
        self::assertTrue($subDir->exists());
        self::assertTrue($subDir->isEmpty());

        $subDirFile = new File($subDir->getPath(), uniqid('dir_test_', true) . '.txt');
        self::assertFalse($subDirFile->exists());
        $subDirFile->write('Hello World');
        self::assertTrue($subDirFile->exists());

        $subSubDir = new Directory($subDir->getPath(), uniqid('dir_test_', true));
        self::assertFalse($subSubDir->exists());
        $subSubDir->create();
        self::assertTrue($subSubDir->exists());
        self::assertTrue($subSubDir->isEmpty());

        $subDir->clear();
        self::assertFalse($subDirFile->exists());
        self::assertFalse($subSubDir->exists());
        self::assertTrue($subDir->isEmpty());
        $subDir->delete();
        self::assertFalse($subDir->exists());
    }
}