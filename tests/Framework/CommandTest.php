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
    private static ?Application $app = null;

    private function getApplication(): Application
    {
        self::$app ??= new Application();

        return self::$app;
    }

    protected function runCommand(string $name, array $args = [], array $inputs = [], array $options = []): CommandResult
    {
        $options['capture_stderr_separately'] = $options['capture_stderr_separately'] ?? true;
        $tester = new CommandTester($this->getApplication()->get($name));
        $tester->setInputs($inputs);
        $exitCode = $tester->execute($args, $options);

        return new CommandResult($exitCode, $tester->getDisplay(), $tester->getErrorOutput());
    }

    protected function getCommandBus(): CommandBus
    {
        return $this->getApplication()->getContainer()->get(CommandBus::class);
    }

    protected function getAuthContext(): AuthContext
    {
        return $this->getApplication()->getAuthContext();
    }

    protected static function assertExecutionSuccess(CommandResult $result): void
    {
        self::assertSame('', trim($result->errorOutput), $result->errorOutput);
        self::assertSame(0, $result->exitCode);
    }

    protected static function assertExecutionFailed(CommandResult $result): void
    {
        self::assertNotSame('', trim($result->errorOutput));
        self::assertNotSame(0, $result->exitCode);
    }
}
