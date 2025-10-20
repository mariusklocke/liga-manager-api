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
        $logoFile = DataGenerator::generateImage($this->getRandomImageType());
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

        return $resultFile;
    }

    #[Depends("testCanBeImported")]
    public function testCanBeCleaned(File $referencedLogoFile): void
    {
        self::assertTrue($referencedLogoFile->exists());
        $logoDirectory = dirname($referencedLogoFile->getPath());
        self::assertDirectoryExists($logoDirectory);
        $imageType = $this->getRandomImageType();
        $staleLogoId = DataGenerator::generateId();
        $staleLogoFile = DataGenerator::generateImage($imageType);
        $staleLogoFile->move($logoDirectory, "$staleLogoId.$imageType");
        self::assertTrue($staleLogoFile->exists());

        $result = $this->runCommand('app:logo:cleanup');
        self::assertExecutionSuccess($result);
        self::assertFalse($staleLogoFile->exists());
        self::assertTrue($referencedLogoFile->exists());
    }

    private function getRandomImageType(): string
    {
        $extensions = ['gif', 'jpg', 'png', 'webp'];

        return $extensions[array_rand($extensions)];
    }
}