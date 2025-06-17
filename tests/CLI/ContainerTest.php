<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use HexagonalPlayground\Tests\Framework\CommandTest;

class ContainerTest extends CommandTest
{
    public function testCanBeInspected(): void
    {
        $result = $this->runCommand('app:container:inspect');
        self::assertExecutionSuccess($result->exitCode);
    }
}