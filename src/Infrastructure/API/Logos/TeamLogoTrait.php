<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Util\Uuid;
use HexagonalPlayground\Infrastructure\Config;
use Psr\Http\Message\UploadedFileInterface;

trait TeamLogoTrait
{
    private TeamRepositoryInterface $teamRepository;

    private function findTeam(array $queryParams): Team
    {
        TypeAssert::assertString($queryParams['teamId'], 'teamId');

        /** @var Team $team */
        $team = $this->teamRepository->find($queryParams['teamId']);

        return $team;
    }

    private function buildStoragePath(string $fileId): string
    {
        return join(DIRECTORY_SEPARATOR, [Config::getInstance()->appLogosPath, "$fileId.webp"]);
    }

    private function buildPublicPath(string $fileId): string
    {
        return join('/', [Config::getInstance()->appLogosPublicPath, "$fileId.webp"]);
    }

    private function deleteLogo(string $fileId): void
    {
        unlink($this->buildStoragePath($fileId));
    }

    private function saveLogo(UploadedFileInterface $uploadedFile): string
    {
        $fileId = Uuid::create();

        $uploadedFile->moveTo($this->buildStoragePath($fileId));

        return $fileId;
    }
}
