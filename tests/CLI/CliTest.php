<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use HexagonalPlayground\Infrastructure\CLI\Bootstrap;
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

    public function testCreatingUser(): void
    {
        $tester = $this->getCommandTester('app:create-user');
        $tester->setInputs(['mary.poppins@example.com', '123456', 'Mary', 'Poppins', 'admin']);

        self::assertExecutionSuccess($tester->execute([]));
    }

    public function testLoadingFixtures(): void
    {
        $tester = $this->getCommandTester('app:load-fixtures');
        self::assertExecutionSuccess($tester->execute([]));
    }

    public function testSeasonsCanBeImportedFromLegacyFiles(): void
    {
        $tester = $this->getCommandTester('app:import-season');

        $exitCode = $tester->execute(['path' => __DIR__ . '/data/*.l98'], ['interactive' => false]);
        $output = $tester->getDisplay();

        self::assertExecutionSuccess($exitCode);
        self::assertStringContainsString('success', $output);
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