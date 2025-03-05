<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\Filesystem\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testCanBeRead(): void
    {
        $file = new File(__FILE__);
        self::assertTrue($file->exists());
        self::assertSame('FileTest.php', $file->getName());
        self::assertSame(__FILE__, $file->getPath());
        self::assertStringContainsString(__NAMESPACE__, $file->read());
        self::assertFalse($file->isEmpty());
        self::assertGreaterThan(0, $file->getSize());

        $stream = $file->open('r');
        self::assertTrue($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertSame($file->read(), $stream->getContents());
        $stream->close();
    }

    public function testCanBeWritten(): void
    {
        $file = new File(sys_get_temp_dir(), uniqid('file_test_', true));
        self::assertFalse($file->exists());
        $file->write('Hello World');
        self::assertTrue($file->exists());
        self::assertSame('Hello World', $file->read());

        $stream = $file->open('w');
        self::assertFalse($stream->isReadable());
        self::assertTrue($stream->isWritable());
        $stream->write('Hello Universe');
        $stream->close();
        self::assertSame('Hello Universe', $file->read());

        $file->clear();
        self::assertTrue($file->isEmpty());
        self::assertSame(0, $file->getSize());
        $file->delete();
        self::assertFalse($file->exists());
    }
}