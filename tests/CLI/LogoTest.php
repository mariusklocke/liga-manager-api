<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use PHPUnit\Framework\Attributes\Depends;
use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Tests\Framework\CommandTest;
use HexagonalPlayground\Tests\Framework\DataGenerator;
use HexagonalPlayground\Tests\Framework\File;

class LogoTest extends CommandTest
{
    public function testCanBeImported(): File
    {
        $teamId = DataGenerator::generateId();
        $this->getCommandBus()->execute(new CreateTeamCommand($teamId, $teamId), $this->getAuthContext());
        $logoFile = File::temp('logo', '.webp');
        $sourceData = DataGenerator::generateBytes(16);
        $logoFile->write($sourceData);
        self::assertTrue($logoFile->exists());

        $result = $this->runCommand('app:logo:import', ['file' => $logoFile->getPath(), 'teamId' => $teamId]);
        self::assertExecutionSuccess($result->exitCode);
        self::assertFalse($logoFile->exists());
        $targetPath = null;
        foreach (preg_split('/\s+/', $result->output) as $word) {
            if (str_ends_with($word, '.webp')) {
                $targetPath = $word;
                break;
            }
        }
        self::assertIsString($targetPath, "Failed to find logo path in \"$result->output\"");
        $resultFile = new File(dirname($targetPath), basename($targetPath));
        self::assertTrue($resultFile->exists());
        $targetData = $resultFile->read();
        self::assertSame($sourceData, $targetData);

        return $resultFile;
    }

    #[Depends("testCanBeImported")]
    public function testCanBeCleaned(File $referencedLogoFile): void
    {
        self::assertTrue($referencedLogoFile->exists());
        $logoDirectory = dirname($referencedLogoFile->getPath());
        self::assertDirectoryExists($logoDirectory);
        $staleLogoId = DataGenerator::generateId();
        $staleLogoFile = new File($logoDirectory, "$staleLogoId.webp");
        $staleLogoData = DataGenerator::generateBytes(16);
        $staleLogoFile->write($staleLogoData);
        self::assertTrue($staleLogoFile->exists());

        $result = $this->runCommand('app:logo:cleanup');
        self::assertExecutionSuccess($result->exitCode);
        self::assertFalse($staleLogoFile->exists());
        self::assertTrue($referencedLogoFile->exists());
    }
}