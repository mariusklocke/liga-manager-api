<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use HexagonalPlayground\Domain\Util\Uuid;
use HexagonalPlayground\Infrastructure\Config;
use Psr\Http\Message\UploadedFileInterface;

class TeamLogoRepository
{
    public function delete(string $logoId): void
    {
        unlink($this->generateStoragePath($logoId));
    }

    public function generatePublicPath(string $logoId): string
    {
        return join('/', [Config::getInstance()->appLogosPublicPath, "$logoId.webp"]);
    }

    private function generateStoragePath(string $logoId): string
    {
        return join(DIRECTORY_SEPARATOR, [Config::getInstance()->appLogosPath, "$logoId.webp"]);
    }

    public function save(UploadedFileInterface $uploadedFile): string
    {
        $logoId = Uuid::create();

        $uploadedFile->moveTo($this->generateStoragePath($logoId));

        return $logoId;
    }
}
