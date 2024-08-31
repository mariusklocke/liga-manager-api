<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use GlobIterator;
use HexagonalPlayground\Infrastructure\CLI\Application;
use HexagonalPlayground\Tests\Framework\DataGenerator;
use PHPUnit\Framework\Attributes\Depends;
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

        $tester = $this->getCommandTester('app:env:setup');
        $tester->setInputs($input);
        self::assertExecutionSuccess($tester->execute([], ['interactive' => true]));
    }

    public function testCheckingHealth(): void
    {
        $tester = $this->getCommandTester('app:health');
        self::assertExecutionSuccess($tester->execute([]));
    }

    public function testWipingDatabase(): void
    {
        $tester = $this->getCommandTester('app:db:wipe');
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
        $tester = $this->getCommandTester('app:user:create');
        $tester->setInputs(['mary.poppins@example.com', DataGenerator::generatePassword(), 'Mary', 'Poppins', 'admin']);
        self::assertExecutionSuccess($tester->execute([]));

        $tester = $this->getCommandTester('app:user:create');
        self::assertExecutionSuccess($tester->execute(['--default' => null]));
    }

    /**
     * @return array
     */
    #[Depends("testCreatingUser")]
    public function testListingUsers(): array
    {
        $tester = $this->getCommandTester('app:user:list');
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
     * @param array $users
     * @return void
     */
    #[Depends("testListingUsers")]
    public function testDeletingUser(array $users): void
    {
        $deletable = array_filter($users, function (array $user) {
            return $user['email'] !== getenv('ADMIN_EMAIL');
        });

        self::assertNotEmpty($deletable);

        $user = array_shift($deletable);
        $tester = $this->getCommandTester('app:user:delete');
        self::assertExecutionSuccess($tester->execute(['userId' => $user['id']]));
    }

    public function testLoadingDemoData(): void
    {
        $tester = $this->getCommandTester('app:db:demo-data');
        self::assertExecutionSuccess($tester->execute([]));
    }

    public function testSeasonsCanBeImportedFromLegacyFiles(): void
    {
        $tester = $this->getCommandTester('app:import:season');
        $files = [];
        foreach (new GlobIterator(__DIR__ . '/data/*.l98') as $fileInfo) {
            $files[] = $fileInfo->getRealPath();
        }
        $exitCode = $tester->execute(['files' => $files], ['interactive' => false]);
        $output = $tester->getDisplay();
        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('success', $output);
    }

    public function testGraphqlSchemaCanBeDumped(): void
    {
        $tester = $this->getCommandTester('app:graphql:schema');
        $exitCode = $tester->execute([]);
        $output = $tester->getDisplay();
        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('mutation', $output);
        self::assertStringContainsString('query', $output);
    }

    public function testSendingMail(): void
    {
        $tester = $this->getCommandTester('app:send-test-mail');

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
