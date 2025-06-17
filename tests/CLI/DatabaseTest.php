<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use PHPUnit\Framework\Attributes\Depends;
use HexagonalPlayground\Tests\Framework\CommandTest;
use HexagonalPlayground\Tests\Framework\File;
use XMLReader;

class DatabaseTest extends CommandTest
{
    public function testCanBeWiped(): void
    {
        $result = $this->runCommand('app:db:wipe', [], ['y'], ['interactive' => true]);
        self::assertExecutionSuccess($result->exitCode);
    }

    public function testCanBeMigrated(): void
    {
        $result = $this->runCommand('app:db:migrate', ['--dry-run' => null]); 
        self::assertExecutionSuccess($result->exitCode);
        self::assertStringContainsString('No queries were executed', $result->output);

        $result = $this->runCommand('app:db:migrate');
        self::assertExecutionSuccess($result->exitCode);
    }

    public function testDemoDataCanBeLoaded(): void
    {
        $result = $this->runCommand('app:db:demo-data');
        self::assertExecutionSuccess($result->exitCode);
    }

    /**
     * @return File
     */
    public function testCanBeExported(): File
    {
        $xmlFile = File::temp('database', '.xml');

        // Test anonymized export
        $result = $this->runCommand('app:db:export', ['file' => $xmlFile->getPath(), '--anonymize' => null]);
        self::assertExecutionSuccess($result->exitCode);
        self::assertStringContainsString('Successfully exported', $result->output);
        self::assertGreaterThan(0, $xmlFile->getSize());
        $nodeCounts = self::countXmlNodes($xmlFile->getPath());
        self::assertSame(1, $nodeCounts['database']);
        self::assertGreaterThan(0, $nodeCounts['table']);
        self::assertGreaterThan(0, $nodeCounts['row']);
        self::assertGreaterThan(0, $nodeCounts['column']);

        // Test regular export
        $xmlFile->delete();
        $result = $this->runCommand('app:db:export', ['file' => $xmlFile->getPath()]);
        self::assertExecutionSuccess($result->exitCode);
        self::assertStringContainsString('Successfully exported', $result->output);
        self::assertGreaterThan(0, $xmlFile->getSize());
        $nodeCounts = self::countXmlNodes($xmlFile->getPath());
        self::assertSame(1, $nodeCounts['database']);
        self::assertGreaterThan(0, $nodeCounts['table']);
        self::assertGreaterThan(0, $nodeCounts['row']);
        self::assertGreaterThan(0, $nodeCounts['column']);

        return $xmlFile;
    }

    /**
     * @param File $xmlFile
     * @return void
     */
    #[Depends("testCanBeExported")]
    public function testCanBeImported(File $xmlFile): void
    {
        $result = $this->runCommand('app:db:import', ['file' => $xmlFile->getPath()]);
        self::assertExecutionSuccess($result->exitCode);
        self::assertStringContainsString('Successfully imported', $result->output);
        $xmlFile->delete();
    }

    protected static function countXmlNodes(string $filePath): array
    {
        $counts = [];
        $reader = new XMLReader();
        $reader->open('file://' . $filePath);
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT) {
                if (!isset($counts[$reader->name])) {
                    $counts[$reader->name] = 0;
                }
                $counts[$reader->name]++;
            }
        }
        $reader->close();
        return $counts;
    }
}