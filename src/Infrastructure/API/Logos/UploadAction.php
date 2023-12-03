<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Domain\Exception\InternalException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\Uuid;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

class UploadAction implements ActionInterface
{
    private string $storageBasePath;
    private string $publicBasePath;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->storageBasePath = '/var/www/logos';
        $this->publicBasePath  = '/logos';
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $file = $this->getUploadedFile($request);

        $this->checkForUploadError($file);

        $fileSize = (int)$file->getSize();
        if ($fileSize === 0) {
            throw new InvalidInputException("Uploaded file is empty");
        }

        $mediaType = $file->getClientMediaType();
        if ($mediaType !== 'image/webp') {
            throw new InvalidInputException("Invalid media type. Expected: image/webp. Got: $mediaType");
        }

        $fileId = $this->generateFileId();

        $file->moveTo($this->buildStoragePath($fileId));

        return $response->withHeader('Location', $this->buildPublicPath($fileId));
    }

    private function buildStoragePath(string $fileId): string
    {
        return join(DIRECTORY_SEPARATOR, [$this->storageBasePath, "$fileId.webp"]);
    }

    private function buildPublicPath(string $fileId): string
    {
        return join('/', [$this->publicBasePath, "$fileId.webp"]);
    }

    private function generateFileId(): string
    {
        return Uuid::create();
    }

    private function getUploadedFile(ServerRequestInterface $request): UploadedFileInterface
    {
        $files = $request->getUploadedFiles();
        $count = count($files);

        if ($count !== 1) {
            throw new InvalidInputException("Invalid upload file count. Expected: 1. Got: $count");
        }

        /** @var UploadedFileInterface $file */
        $file = array_shift($files);

        return $file;
    }

    private function checkForUploadError(UploadedFileInterface $uploadedFile): void
    {
        $errorCode = $uploadedFile->getError();
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                return;
            case UPLOAD_ERR_INI_SIZE:
                $maxSize = ini_get('upload_max_filesize');
                throw new InvalidInputException("Invalid file upload: File exceeds max size of $maxSize");
            default:
                $this->logger->error("Unexpected file upload error", [
                    'errorCode' => $errorCode,
                    'files' => $_FILES
                ]);
                throw new InternalException("Invalid file upload: Code $errorCode");
        }
    }
}
