<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use HexagonalPlayground\Tests\Framework\CommandTest;

class EnvTest extends CommandTest
{
    public function testCanBeSetUp(): void
    {
        $input = [
            'notice',
            'php://stdout',
            'redis',
            'mariadb',
            'db1',
            'user',
            'password',
            'noreply@example.com',
            'No Reply',
            'smtp://127.0.0.1:25'
        ];
        $result = $this->runCommand('app:env:setup', [], $input, ['interactive' => true]);
        self::assertExecutionSuccess($result->exitCode);
    }
}