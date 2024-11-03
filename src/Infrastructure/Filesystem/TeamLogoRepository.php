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
        $this->filesystemService->deleteFile($this->generatePrivatePath($logoId));
    }

    public function generatePublicPath(string $logoId): string
    {
        return join('/', [
            $this->config->getValue('app.logos.public.path', '/logos'),
            "$logoId.webp"
        ]);
    }

    public function generatePrivatePath(string $logoId): string
    {
        return $this->filesystemService->joinPaths([
            $this->config->getValue('app.logos.path', ''),
            "$logoId.webp"
        ]);
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
        $directory = $this->config->getValue('app.logos.path', '');
        foreach ($this->filesystemService->getDirectoryIterator($directory) as $filename) {
            if (str_ends_with($filename, '.webp')) {
                $logoIds[] = str_replace('.webp', '', $filename);
            }
        }
        return $logoIds;
    }
}
