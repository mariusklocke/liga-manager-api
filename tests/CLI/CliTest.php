<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use HexagonalPlayground\Infrastructure\CLI\Bootstrap;
use HexagonalPlayground\Infrastructure\CLI\CreateUserCommand;
use HexagonalPlayground\Infrastructure\CLI\DebugGqlSchemaCommand;
use HexagonalPlayground\Infrastructure\CLI\L98ImportCommand;
use HexagonalPlayground\Infrastructure\CLI\LoadFixturesCommand;
use HexagonalPlayground\Infrastructure\CLI\MaintenanceModeCommand;
use HexagonalPlayground\Infrastructure\CLI\SendTestMailCommand;
use HexagonalPlayground\Infrastructure\CLI\SetupDbCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CliTest extends TestCase
{
    /** @var Application */
    private $app;

    protected function setUp(): void
    {
        $this->app = Bootstrap::bootstrap();
    }

    public function testSetupDatabase(): void
    {
        $tester = $this->getCommandTester(SetupDbCommand::NAME);

        self::assertExecutionSuccess($tester->execute([]));
    }

    /**
     * @depends testSetupDatabase
     */
    public function testCreatingUser(): void
    {
        $tester = $this->getCommandTester(CreateUserCommand::NAME);
        $tester->setInputs(['mary.poppins@example.com', '123456', 'Mary', 'Poppins', 'admin']);

        self::assertExecutionSuccess($tester->execute([]));
    }

    /**
     * @depends testSetupDatabase
     */
    public function testLoadingFixtures(): void
    {
        $tester = $this->getCommandTester(LoadFixturesCommand::NAME);
        self::assertExecutionSuccess($tester->execute([]));
    }

    /**
     * @depends testSetupDatabase
     */
    public function testSeasonsCanBeImportedFromLegacyFiles(): void
    {
        $tester = $this->getCommandTester(L98ImportCommand::NAME);

        $exitCode = $tester->execute(['path' => __DIR__ . '/data/*.l98'], ['interactive' => false]);
        $output = $tester->getDisplay();

        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('success', $output);
    }

    public function testGraphqlSchemaCanBeDumped(): void
    {
        $tester = $this->getCommandTester(DebugGqlSchemaCommand::NAME);

        $exitCode = $tester->execute([]);
        $output = $tester->getDisplay();

        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('mutation', $output);
        self::assertStringContainsString('query', $output);
    }

    public function testMaintenanceMode(): void
    {
        $tester = $this->getCommandTester(MaintenanceModeCommand::NAME);

        // Has to be off by default
        $exitCode = $tester->execute([]);
        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('Maintenance mode is off', $tester->getDisplay());

        // Can be enabled
        $exitCode = $tester->execute(['--mode' => 'on']);
        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('Maintenance mode has been enabled', $tester->getDisplay());

        // Can be disabled
        $exitCode = $tester->execute(['--mode' => 'off']);
        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('Maintenance mode has been disabled', $tester->getDisplay());
    }

    public function testSendingMail(): void
    {
        $tester = $this->getCommandTester(SendTestMailCommand::NAME);

        self::assertExecutionSuccess($tester->execute(['recipient' => 'test@example.com']));
    }

    private function getCommandTester(string $commandName): CommandTester
    {
        return new CommandTester($this->app->get($commandName));
    }

    private static function assertExecutionSuccess(int $exitCode): void
    {
        self::assertEquals(0, $exitCode);
    }
}
