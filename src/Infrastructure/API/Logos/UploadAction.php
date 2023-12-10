<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Exception\InternalException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\Security\AuthorizationTrait;
use HexagonalPlayground\Infrastructure\Filesystem\TeamLogoRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

class UploadAction implements ActionInterface
{
    use TeamFinderTrait;
    use AuthorizationTrait;
    private TeamLogoRepository $teamLogoRepository;
    private LoggerInterface $logger;

    public function __construct(TeamRepositoryInterface $teamRepository, TeamLogoRepository $teamLogoRepository, LoggerInterface $logger)
    {
        $this->teamRepository = $teamRepository;
        $this->teamLogoRepository = $teamLogoRepository;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->assertIsAdmin($request);

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
            $this->teamLogoRepository->delete($team->getLogoId());
        }

        $fileId = $this->teamLogoRepository->save($file);
        $publicPath = $this->teamLogoRepository->generatePublicPath($fileId);
        $team->setLogoId($fileId);
        $this->teamRepository->save($team);
        $this->teamRepository->flush();

        $this->logger->info("Uploaded team logo has been saved", [
            'teamId' => $team->getId(),
            'fileId' => $team->getLogoId()
        ]);

        return $response->withStatus(201)->withHeader('Location', $publicPath);
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
