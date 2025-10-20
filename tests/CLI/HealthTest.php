<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use HexagonalPlayground\Tests\Framework\CommandTest;

class HealthTest extends CommandTest
{
    public function testCanBeChecked(): void
    {
        $result = $this->runCommand('app:health:check');
        self::assertExecutionSuccess($result);
    }
}