<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use HexagonalPlayground\Domain\Util\Uuid;
use HexagonalPlayground\Infrastructure\Config;
use Psr\Http\Message\UploadedFileInterface;

class TeamLogoRepository
{
    private FilesystemService $filesystemService;
    private Config $config;

    public function __construct(FilesystemService $filesystemService, Config $config)
    {
        $this->filesystemService = $filesystemService;
        $this->config = $config;
    }

    public function delete(string $logoId): void
    {
        $this->filesystemService->deleteFile($this->generateStoragePath($logoId));
    }

    public function generatePublicPath(string $logoId): string
    {
        return join('/', [$this->config->appLogosPublicPath, "$logoId.webp"]);
    }

    private function generateStoragePath(string $logoId): string
    {
        return $this->filesystemService->joinPaths([$this->config->appLogosPath, "$logoId.webp"]);
    }

    public function save(UploadedFileInterface $uploadedFile): string
    {
        $logoId = Uuid::create();

        $uploadedFile->moveTo($this->generateStoragePath($logoId));

        return $logoId;
    }
}
