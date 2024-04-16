<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use HexagonalPlayground\Domain\Util\Uuid;
use Psr\Http\Message\UploadedFileInterface;

class TeamLogoRepository
{
    private FilesystemService $filesystemService;
    private string $appLogosPath;
    private string $appLogosPublicPath;

    public function __construct(FilesystemService $filesystemService, string $appLogosPath, string $appLogosPublicPath)
    {
        $this->filesystemService = $filesystemService;
        $this->appLogosPath = $appLogosPath;
        $this->appLogosPublicPath = $appLogosPublicPath;
    }

    public function delete(string $logoId): void
    {
        $this->filesystemService->deleteFile($this->generateStoragePath($logoId));
    }

    public function generatePublicPath(string $logoId): string
    {
        return join('/', [$this->appLogosPublicPath, "$logoId.webp"]);
    }

    private function generateStoragePath(string $logoId): string
    {
        return $this->filesystemService->joinPaths([$this->appLogosPath, "$logoId.webp"]);
    }

    public function save(UploadedFileInterface $uploadedFile): string
    {
        $logoId = Uuid::create();

        $uploadedFile->moveTo($this->generateStoragePath($logoId));

        return $logoId;
    }
}
