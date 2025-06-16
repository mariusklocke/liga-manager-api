<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Infrastructure\CLI\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
    }

    protected function runCommand(string $name, array $args = [], array $inputs = [], array $options = []): CommandResult
    {
        $options['capture_stderr_separately'] = $options['capture_stderr_separately'] ?? true;
        $tester = new CommandTester($this->app->get($name));
        $tester->setInputs($inputs);
        $exitCode = $tester->execute($args, $options);

        return new CommandResult($exitCode, $tester->getDisplay(), $tester->getErrorOutput());
    }

    protected function getCommandBus(): CommandBus
    {
        return $this->app->getContainer()->get(CommandBus::class);
    }

    protected function getAuthContext(): AuthContext
    {
        return $this->app->getAuthContext();
    }

    protected static function assertExecutionSuccess(int $exitCode): void
    {
        self::assertEquals(0, $exitCode);
    }

    protected static function assertExecutionFailed(int $exitCode): void
    {
        self::assertNotEquals(0, $exitCode);
    }
}
