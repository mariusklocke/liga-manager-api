<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use HexagonalPlayground\Tests\Framework\CommandTest;

class ConfigTest extends CommandTest
{
    public function testCanBeShown(): void
    {
        $result = $this->runCommand('app:config:show');
        self::assertExecutionSuccess($result->exitCode);
    }

    public function testCanBeValidated(): void
    {
        $result = $this->runCommand('app:config:validate');
        self::assertExecutionSuccess($result->exitCode);
        self::assertStringContainsString('The config is valid', $result->output);
    }
}