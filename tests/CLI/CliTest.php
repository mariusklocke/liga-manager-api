<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use GlobIterator;
use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Infrastructure\CLI\Application;
use HexagonalPlayground\Tests\Framework\DataGenerator;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Type\VoidType;
use Symfony\Component\Console\Tester\CommandTester;
use XMLReader;

class CliTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
    }

    public function testShowConfig(): void
    {
        $tester = $this->getCommandTester('app:config:show');
        $exitCode = $tester->execute([]);

        self::assertExecutionSuccess($exitCode);
    }

    public function testValidatingConfig(): void
    {
        $tester = $this->getCommandTester('app:config:validate');
        $exitCode = $tester->execute([]);
        $output = $tester->getDisplay();

        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('The config is valid', $output);
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
        $tester = $this->getCommandTester('app:health:check');
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
        $tester = $this->getCommandTester('app:db:migrate');
        self::assertExecutionSuccess($tester->execute(['--dry-run' => null]));
        self::assertStringContainsString('No queries were executed', $tester->getDisplay());

        $tester = $this->getCommandTester('app:db:migrate');
        self::assertExecutionSuccess($tester->execute([]));
    }

    public function testCreatingUser(): void
    {
        $tester = $this->getCommandTester('app:user:create');
        $tester->setInputs(['mary.poppins@example.com', DataGenerator::generatePassword(), 'Mary', 'Poppins', 'admin', 'en']);
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

    /**
     * @return string
     */
    public function testDatabaseCanBeExported(): string
    {
        $xmlFile = tempnam(sys_get_temp_dir(), 'database');

        // Test anonymized export
        $tester = $this->getCommandTester('app:db:export');
        $exitCode = $tester->execute(['file' => $xmlFile, '--anonymize' => null]);
        $output = $tester->getDisplay();
        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('Successfully exported', $output);
        self::assertGreaterThan(0, filesize($xmlFile));
        $nodeCounts = self::countXmlNodes($xmlFile);
        self::assertSame(1, $nodeCounts['database']);
        self::assertGreaterThan(0, $nodeCounts['table']);
        self::assertGreaterThan(0, $nodeCounts['row']);
        self::assertGreaterThan(0, $nodeCounts['column']);

        // Test regular export
        unlink($xmlFile);
        $tester = $this->getCommandTester('app:db:export');
        $exitCode = $tester->execute(['file' => $xmlFile]);
        $output = $tester->getDisplay();
        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('Successfully exported', $output);
        self::assertGreaterThan(0, filesize($xmlFile));
        $nodeCounts = self::countXmlNodes($xmlFile);
        self::assertSame(1, $nodeCounts['database']);
        self::assertGreaterThan(0, $nodeCounts['table']);
        self::assertGreaterThan(0, $nodeCounts['row']);
        self::assertGreaterThan(0, $nodeCounts['column']);

        return $xmlFile;
    }

    /**
     * @param string $xmlFile
     * @return void
     */
    #[Depends("testDatabaseCanBeExported")]
    public function testDatabaseCanBeImported(string $xmlFile): void
    {
        $tester = $this->getCommandTester('app:db:import');
        $exitCode = $tester->execute(['file' => $xmlFile]);
        $output = $tester->getDisplay();
        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('Successfully imported', $output);
        unlink($xmlFile);
    }

    public function testSendingMail(): void
    {
        $tester = $this->getCommandTester('app:mail:send');
        self::assertExecutionSuccess($tester->execute([
            'recipient' => 'test@example.com',
            'subject' => 'Test',
            'content' => 'This is just a test'
        ]));
    }

    public function testImportingLogo(): string
    {
        $teamId = DataGenerator::generateId();
        $this->getCommandBus()->execute(new CreateTeamCommand($teamId, $teamId), $this->getAuthContext());
        $sourcePath = tempnam(sys_get_temp_dir(), 'logo');
        $sourceData = DataGenerator::generateBytes(16);
        file_put_contents($sourcePath, $sourceData);
        self::assertFileExists($sourcePath);

        $tester = $this->getCommandTester('app:logo:import');
        $exitCode = $tester->execute(['file' => $sourcePath, 'teamId' => $teamId]);
        $output = $tester->getDisplay();

        self::assertExecutionSuccess($exitCode);
        self::assertFileDoesNotExist($sourcePath);
        $targetPath = null;
        foreach (preg_split('/\s+/', $output) as $word) {
            if (str_ends_with($word, '.webp')) {
                $targetPath = $word;
                break;
            }
        }
        self::assertIsString($targetPath, "Failed to find logo path in \"$output\"");
        self::assertFileExists($targetPath);
        $targetData = file_get_contents($targetPath);
        self::assertSame($sourceData, $targetData);

        return $targetPath;
    }

    #[Depends("testImportingLogo")]
    public function testCleanupLogo(string $referencedLogoPath): void
    {
        self::assertFileExists($referencedLogoPath);
        $logoDirectory = dirname($referencedLogoPath);
        self::assertDirectoryExists($logoDirectory);
        $staleLogoId = DataGenerator::generateId();
        $staleLogoPath = join(DIRECTORY_SEPARATOR, [$logoDirectory, "$staleLogoId.webp"]);
        $staleLogoData = DataGenerator::generateBytes(16);
        file_put_contents($staleLogoPath, $staleLogoData);
        self::assertFileExists($staleLogoPath);

        $tester = $this->getCommandTester('app:logo:cleanup');
        $exitCode = $tester->execute([]);

        self::assertExecutionSuccess($exitCode);
        self::assertFileDoesNotExist($staleLogoPath);
        self::assertFileExists($referencedLogoPath);
    }

    private function getCommandTester(string $commandName): CommandTester
    {
        return new CommandTester($this->app->get($commandName));
    }

    private function getCommandBus(): CommandBus
    {
        return $this->app->getContainer()->get(CommandBus::class);
    }

    private function getAuthContext(): AuthContext
    {
        return $this->app->getAuthContext();
    }

    private static function assertExecutionSuccess(int $exitCode): void
    {
        self::assertEquals(0, $exitCode);
    }

    private static function countXmlNodes(string $filePath): array
    {
        $counts = [];
        $reader = new XMLReader();
        $reader->open('file://' . $filePath);
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT) {
                if (!isset($counts[$reader->name])) {
                    $counts[$reader->name] = 0;
                }
                $counts[$reader->name]++;
            }
        }
        $reader->close();
        return $counts;
    }
}
