<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use HexagonalPlayground\Tests\Framework\CommandTest;

class VersionTest extends CommandTest
{
    public function testCanBeListed(): void
    {
        $result = $this->runCommand('app:versions:list');
        self::assertExecutionSuccess($result->exitCode);
    }
}