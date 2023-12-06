<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Exception\InternalException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

class UploadAction implements ActionInterface
{
    use TeamLogoTrait;
    private LoggerInterface $logger;

    public function __construct(TeamRepositoryInterface $teamRepository, LoggerInterface $logger)
    {
        $this->teamRepository = $teamRepository;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $team = $this->findTeam($request->getQueryParams());
        $file = $this->getUploadedFile($request);

        $this->logger->info("Processing team logo upload", [
            'teamId' => $team->getId(),
            'uploadedFile' => [
                'size' => $file->getSize(),
                'error' => $file->getError(),
                'clientFilename' => $file->getClientFilename(),
                'clientMediaType' => $file->getClientMediaType()
            ]
        ]);

        $this->checkUploadedFile($file);

        if ($team->getLogoId() !== null) {
            $this->deleteLogo($team->getLogoId());
        }

        $fileId = $this->saveLogo($file);
        $team->setLogoId($fileId);
        $this->teamRepository->save($team);

        $this->logger->info("Uploaded team logo has been saved", [
            'teamId' => $team->getId(),
            'fileId' => $team->getLogoId()
        ]);

        return $response->withStatus(201);
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

    private function checkUploadedFile(UploadedFileInterface $uploadedFile): void
    {
        $errorCode = $uploadedFile->getError();
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
                $maxSize = ini_get('upload_max_filesize');
                throw new InvalidInputException("Invalid file upload: File exceeds max size of $maxSize");
            default:
                throw new InternalException("Invalid file upload: Code $errorCode");
        }

        $fileSize = (int)$uploadedFile->getSize();
        if ($fileSize === 0) {
            throw new InvalidInputException("Uploaded file is empty");
        }

        $mediaType = $uploadedFile->getClientMediaType();
        if ($mediaType !== 'image/webp') {
            throw new InvalidInputException("Invalid media type. Expected: image/webp. Got: $mediaType");
        }
    }
}
