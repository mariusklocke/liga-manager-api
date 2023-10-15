<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use HexagonalPlayground\Infrastructure\CLI\Application;
use HexagonalPlayground\Infrastructure\CLI\CreateUserCommand;
use HexagonalPlayground\Infrastructure\CLI\DeleteUserCommand;
use HexagonalPlayground\Infrastructure\CLI\ListUserCommand;
use HexagonalPlayground\Infrastructure\CLI\PrintGraphQlSchemaCommand;
use HexagonalPlayground\Infrastructure\CLI\HealthCommand;
use HexagonalPlayground\Infrastructure\CLI\L98ImportCommand;
use HexagonalPlayground\Infrastructure\CLI\LoadDemoDataCommand;
use HexagonalPlayground\Infrastructure\CLI\MaintenanceModeCommand;
use HexagonalPlayground\Infrastructure\CLI\SendTestMailCommand;
use HexagonalPlayground\Infrastructure\CLI\SetupEnvCommand;
use HexagonalPlayground\Infrastructure\CLI\WipeDbCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CliTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
    }

    public function testSetupEnv(): void
    {
        $input = [
            'notice',
            'redis',
            'mariadb',
            'db1',
            'user',
            'password',
            'noreply@example.com',
            'No Reply',
            'smtp://127.0.0.1:25'
        ];

        $tester = $this->getCommandTester(SetupEnvCommand::NAME);
        $tester->setInputs($input);
        self::assertExecutionSuccess($tester->execute([], ['interactive' => true]));
    }

    public function testCheckingHealth(): void
    {
        $tester = $this->getCommandTester(HealthCommand::NAME);
        self::assertExecutionSuccess($tester->execute([]));
    }

    public function testWipingDatabase(): void
    {
        $tester = $this->getCommandTester(WipeDbCommand::NAME);
        $tester->setInputs(['y']);
        self::assertExecutionSuccess($tester->execute([], ['interactive' => true]));
    }

    public function testMigratingDatabase(): void
    {
        $tester = $this->getCommandTester('migrations:migrate');
        self::assertExecutionSuccess($tester->execute(['-n' => null]));
    }

    public function testCreatingUser(): void
    {
        $tester = $this->getCommandTester(CreateUserCommand::NAME);
        $tester->setInputs(['mary.poppins@example.com', '123456', 'Mary', 'Poppins', 'admin']);
        self::assertExecutionSuccess($tester->execute([]));

        $tester = $this->getCommandTester(CreateUserCommand::NAME);
        self::assertExecutionSuccess($tester->execute(['--default' => null]));
    }

    /**
     * @depends testCreatingUser
     * @return array
     */
    public function testListingUsers(): array
    {
        $tester = $this->getCommandTester(ListUserCommand::NAME);
        $exitCode = $tester->execute([]);
        $output = $tester->getDisplay();

        $users = [];

        foreach (explode("\n", trim($output)) as $line) {
            if (str_contains($line, '@')) {
                $columns = array_values(array_filter(explode(' ', $line)));
                $users[] = [
                    'id' => $columns[0],
                    'email' => $columns[1]
                ];
            }
        }

        self::assertExecutionSuccess($exitCode);

        return $users;
    }

    /**
     * @depends testListingUsers
     * @param array $users
     * @return void
     */
    public function testDeletingUser(array $users): void
    {
        $deletable = array_filter($users, function (array $user) {
            return $user['email'] !== getenv('ADMIN_EMAIL');
        });

        self::assertNotEmpty($deletable);

        $user = array_shift($deletable);
        $tester = $this->getCommandTester(DeleteUserCommand::NAME);
        self::assertExecutionSuccess($tester->execute(['userId' => $user['id']]));
    }

    public function testLoadingDemoData(): void
    {
        $tester = $this->getCommandTester(LoadDemoDataCommand::NAME);
        self::assertExecutionSuccess($tester->execute([]));
    }

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
        $tester = $this->getCommandTester(PrintGraphQlSchemaCommand::NAME);
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
