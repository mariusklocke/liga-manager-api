<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Infrastructure\CLI\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
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
        if (!extension_loaded('xdebug')) {
            return $this->runCommandIsolated($name, $args, $inputs, $options);
        }

        $options['capture_stderr_separately'] = $options['capture_stderr_separately'] ?? true;
        $tester = new CommandTester($this->getApplication()->get($name));
        $tester->setInputs($inputs);
        $exitCode = $tester->execute($args, $options);

        return new CommandResult($exitCode, $tester->getDisplay(), $tester->getErrorOutput());
    }

    protected function runCommandIsolated(string $name, array $args = [], array $inputs = [], array $options = []): CommandResult
    {
        $mappedArgs = [];
        foreach ($args as $key => $value) {
            // Handle options (e.g. --option=value or --option value)
            if (str_starts_with($key, '--')) {
                $mappedArgs[] = $key;
                if ($value !== null) {
                    $mappedArgs[] = $value;
                }
                continue;
            }

            // Handle positional arguments
            if (is_array($value)) {
                foreach ($value as $item) {
                    $mappedArgs[] = $item;
                }
            } else {
                $mappedArgs[] = $value;
            }
        }

        switch ($options['verbosity'] ?? null) {
            case OutputInterface::VERBOSITY_VERBOSE:
                $mappedArgs[] = '-v';
                break;
            case OutputInterface::VERBOSITY_VERY_VERBOSE:
                $mappedArgs[] = '-vv';
                break;
            case OutputInterface::VERBOSITY_DEBUG:
                $mappedArgs[] = '-vvv';
                break;
        }

        if (isset($options['interactive']) && $options['interactive'] === false) {
            $mappedArgs[] = '--no-interaction';
        }

        $command = array_merge(['php', 'app.phar', $name], $mappedArgs);
        $process = new \Symfony\Component\Process\Process($command);
        if (count($inputs) > 0) {
            $process->setInput(implode("\n", $inputs));
        }
        $exitCode = $process->run();

        return new CommandResult($exitCode, $process->getOutput(), $process->getErrorOutput());
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
