<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Unit;

use HexagonalPlayground\Infrastructure\Filesystem\FileStream;
use PHPUnit\Framework\TestCase;

class FileStreamTest extends TestCase
{
    /** @var string */
    private static $filePath;

    /** @var string */
    private static $fileData;

    /**
     * @beforeClass
     */
    public static function generateTempFilePath()
    {
        self::$filePath = tempnam(sys_get_temp_dir(), '');
        self::$fileData = __CLASS__;
    }

    public function testFileCanBeWritten(): void
    {
        $stream = new FileStream(self::$filePath, 'w');
        self::assertFalse($stream->isReadable());
        self::assertTrue($stream->isWritable());
        self::assertSame(0, $stream->getSize());
        self::assertSame(\strlen(self::$fileData), $stream->write(self::$fileData));
        self::assertSame(\strlen(self::$fileData), $stream->tell());
        self::assertSame(\strlen(self::$fileData), $stream->getSize());
        $stream->close();
    }

    /**
     * @depends testFileCanBeWritten
     */
    public function testFileCanBeRead(): void
    {
        $stream = new FileStream(self::$filePath, 'r');
        self::assertFalse($stream->isWritable());
        self::assertTrue($stream->isReadable());
        self::assertTrue($stream->isSeekable());
        self::assertFalse($stream->eof());
        self::assertSame(\strlen(self::$fileData), $stream->getSize());
        self::assertSame(self::$fileData, (string) $stream);
        self::assertTrue($stream->eof());
        self::assertSame(\strlen(self::$fileData), $stream->tell());
        $stream->close();
    }
}