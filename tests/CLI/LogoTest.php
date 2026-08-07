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
    private const IMAGE_TYPES = [
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    public function testCanBeImported(): File
    {
        $teamId = DataGenerator::generateId();
        $this->getCommandBus()->execute(new CreateTeamCommand($teamId, $teamId), $this->getAuthContext());
        $resultFile = null;
        foreach (self::IMAGE_TYPES as $extension) {
            $logoFile = DataGenerator::generateImage($extension);
            self::assertTrue($logoFile->exists());
            $sourceData = $logoFile->read();
            $result = $this->runCommand('app:logo:import', ['file' => $logoFile->getPath(), 'teamId' => $teamId]);
            self::assertExecutionSuccess($result);
            self::assertFalse($logoFile->exists());
            preg_match("/Path: (\S+)/", $result->output, $matches);
            self::assertIsString($matches[1], "Failed to find logo path in \"$result->output\"");
            $targetPath = $matches[1];
            $resultFile = new File(dirname($targetPath), basename($targetPath));
            self::assertTrue($resultFile->exists());
            $targetData = $resultFile->read();
            self::assertSame($sourceData, $targetData);
        }

        return $resultFile;
    }

    #[Depends("testCanBeImported")]
    public function testCanBeCleaned(File $referencedLogoFile): void
    {
        self::assertTrue($referencedLogoFile->exists());
        $logoDirectory = dirname($referencedLogoFile->getPath());
        self::assertDirectoryExists($logoDirectory);
        foreach (self::IMAGE_TYPES as $extension) {
            $staleLogoFile = DataGenerator::generateImage($extension);
            $staleLogoFile->move($logoDirectory, DataGenerator::generateId() . ".$extension");
            self::assertTrue($staleLogoFile->exists());

            $result = $this->runCommand('app:logo:cleanup');
            self::assertExecutionSuccess($result);
            self::assertFalse($staleLogoFile->exists());
            self::assertTrue($referencedLogoFile->exists());
        }

    }
}