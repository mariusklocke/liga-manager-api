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
        $this->filesystemService->deleteFile($this->generatePrivatePath($logoId));
    }

    public function generatePublicPath(string $logoId): string
    {
        return join('/', [$this->appLogosPublicPath, "$logoId.webp"]);
    }

    public function generatePrivatePath(string $logoId): string
    {
        return $this->filesystemService->joinPaths([$this->appLogosPath, "$logoId.webp"]);
    }

    public function save(UploadedFileInterface $uploadedFile): string
    {
        $logoId = Uuid::create();

        $uploadedFile->moveTo($this->generatePrivatePath($logoId));

        return $logoId;
    }

    public function findAll(): array
    {
        $logoIds = [];
        foreach ($this->filesystemService->getDirectoryIterator($this->appLogosPath) as $filename) {
            if (str_ends_with($filename, '.webp')) {
                $logoIds[] = str_replace('.webp', '', $filename);
            }
        }
        return $logoIds;
    }
}
