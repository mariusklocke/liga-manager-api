<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

use HexagonalPlayground\Infrastructure\CLI\Bootstrap;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CliTest extends \PHPUnit\Framework\TestCase
{
    /** @var Application */
    private $app;

    protected function setUp(): void
    {
        $this->app = Bootstrap::bootstrap();
    }

    public function testCreatingUser()
    {
        $tester = $this->getCommandTester('app:create-user');
        $tester->setInputs(['mary.poppins@example.com', '123456', 'Mary', 'Poppins', 'admin']);

        self::assertExecutionSuccess($tester->execute([]));

    }

    private function getCommandTester(string $commandName): CommandTester
    {
        return new CommandTester($this->app->get($commandName));
    }

    private static function assertExecutionSuccess(int $exitCode): void
    {
        self::assertEquals(0, $exitCode);
    }

    private static function assertExecutionFailed(int $exitCode): void
    {
        self::assertNotEquals(0, $exitCode);
    }
}