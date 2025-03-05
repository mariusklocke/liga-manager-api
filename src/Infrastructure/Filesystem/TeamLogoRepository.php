<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use HexagonalPlayground\Domain\Util\Uuid;
use HexagonalPlayground\Infrastructure\Config;
use Psr\Http\Message\UploadedFileInterface;

class TeamLogoRepository
{
    private Directory $storageDirectory;
    private string $publicPath;

    public function __construct(Config $config)
    {
        $this->storageDirectory = new Directory($config->getValue('app.logos.path', ''));
        $this->publicPath = $config->getValue('app.logos.public.path', '/logos');
    }

    public function delete(string $logoId): void
    {
        $file = $this->getStorageFile($logoId);
        $file->delete();
    }

    public function generatePublicPath(string $logoId): string
    {
        return join('/', [$this->publicPath, "$logoId.webp"]);
    }

    public function getStorageFile(string $logoId): File
    {
        return new File($this->storageDirectory->getPath(), "$logoId.webp");
    }

    public function save(UploadedFileInterface $uploadedFile): string
    {
        $logoId = Uuid::create();

        $uploadedFile->moveTo($this->getStorageFile($logoId)->getPath());

        return $logoId;
    }

    public function findAll(): array
    {
        $logoIds = [];

        foreach ($this->storageDirectory->list() as $item) {
            if ($item instanceof File && str_ends_with($item->getName(), '.webp')) {
                $logoIds[] = str_replace('.webp', '', $item->getName());
            }
        }

        return $logoIds;
    }
}
