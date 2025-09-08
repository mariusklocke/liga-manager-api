<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Value\Uuid;
use HexagonalPlayground\Infrastructure\Config;
use Psr\Http\Message\UploadedFileInterface;

class TeamLogoRepository
{
    private Directory $storageDirectory;
    private string $publicPath;
    private array $imageTypes;

    public function __construct(Config $config)
    {
        $this->storageDirectory = new Directory($config->getValue('app.logos.path', ''));
        $this->publicPath = $config->getValue('app.logos.public.path', '/logos');
        $this->imageTypes = [
            'image/gif' => 'gif',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];
    }

    public function delete(string $logoId): void
    {
        try {
            $file = $this->getStorageFile($logoId);
        } catch (NotFoundException) {
            return;
        }
        $file->delete();
    }

    public function generatePublicPath(string $logoId): string
    {
        return join('/', [$this->publicPath, $this->getStorageFile($logoId)->getName()]);
    }

    public function getStorageFile(string $logoId): File
    {
        foreach ($this->storageDirectory->list() as $item) {
            if ($item instanceof File && str_starts_with($item->getName(), $logoId)) {
                return $item;
            }
        }
        throw new NotFoundException("Cannot find logo file with ID $logoId");
    }

    public function save(UploadedFileInterface $uploadedFile): string
    {
        $this->checkUploadedFile($uploadedFile);
        $mediaType = $uploadedFile->getClientMediaType();
        $fileExtension = $this->imageTypes[$mediaType];
        $logoId = (string)Uuid::generate();
        $targetFile = new File(
            $this->storageDirectory->getPath(),
            sprintf('%s.%s', $logoId, $fileExtension)
        );
        $uploadedFile->moveTo($targetFile->getPath());

        return $logoId;
    }

    public function findAll(): array
    {
        $logoIds = [];

        foreach ($this->storageDirectory->list() as $item) {
            if (!$item instanceof File) {
                continue;
            }

            list($id, $extension) = explode('.', $item->getName());
            if (!in_array($extension, $this->imageTypes)) {
                continue;
            }

            $logoIds[] = $id;
        }

        return $logoIds;
    }

    private function checkUploadedFile(UploadedFileInterface $uploadedFile): void
    {
        $maxFileSize = $this->parseByteSize(ini_get('upload_max_filesize'));
        $fileSize = (int)$uploadedFile->getSize();
        $mediaType = $uploadedFile->getClientMediaType();

        // Assert does not exceed max file size
        Assert::true(
            $fileSize <= $maxFileSize && $uploadedFile->getError() !== UPLOAD_ERR_INI_SIZE,
            "Invalid file upload: Exceeds max size of $maxFileSize bytes",
            InvalidInputException::class
        );

        // Assert not empty
        Assert::true(
            $fileSize > 0,
            "Invalid file upload: File is empty",
            InvalidInputException::class
        );

        // Assert no unexpected error
        Assert::true(
            $uploadedFile->getError() === UPLOAD_ERR_OK,
            "Invalid file upload: Code " . $uploadedFile->getError(),
            InvalidInputException::class
        );

        // Assert has media type
        Assert::true(
            $mediaType !== null,
            "Missing media type",
            InvalidInputException::class
        );
 
        // Assert media type is supported
        Assert::true(
            array_key_exists($mediaType, $this->imageTypes),
            "Unsupported media type: $mediaType",
            InvalidInputException::class
        );
    }

    /**
     * Converts a byte size string with SI-prefixes to number of bytes
     *
     * @param string $byteSize
     * @return int
     */
    private function parseByteSize(string $byteSize): int
    {
        $factorMap = [
            'K' => pow(2, 10),
            'M' => pow(2, 20),
            'G' => pow(2, 30)
        ];

        $prefix = $byteSize[strlen($byteSize) - 1];
        if (!array_key_exists($prefix, $factorMap)) {
            return (int)$byteSize;
        }

        return substr($byteSize, 0, -1) * $factorMap[$prefix];
    }
}
