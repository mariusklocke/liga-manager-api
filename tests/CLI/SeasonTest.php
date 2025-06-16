<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use GlobIterator;
use HexagonalPlayground\Tests\Framework\CommandTest;

class SeasonTest extends CommandTest
{
    public function testCanBeImported(): void
    {
        $files = [];
        foreach (new GlobIterator(__DIR__ . '/data/*.l98') as $fileInfo) {
            $files[] = $fileInfo->getRealPath();
        }
        $result = $this->runCommand('app:import:season', ['files' => $files], [], ['interactive' => false]);
        self::assertExecutionSuccess($result->exitCode);
        self::assertStringContainsString('success', $result->output);
    }
}