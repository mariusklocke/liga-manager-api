<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use HexagonalPlayground\Tests\Framework\CommandTest;

class MailTest extends CommandTest
{
    public function testCanBeSent(): void
    {
        $result = $this->runCommand('app:mail:send', [
            'recipient' => 'test@example.com',
            'subject' => 'Test',
            'content' => 'This is just a test'
        ]);
        self::assertExecutionSuccess($result);
    }
}