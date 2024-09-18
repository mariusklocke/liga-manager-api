<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Exception\InternalException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\Security\AuthorizationTrait;
use HexagonalPlayground\Infrastructure\Filesystem\TeamLogoRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

class Controller extends BaseController
{
    use AuthorizationTrait;
    use TeamFinderTrait;
    private TeamLogoRepository $teamLogoRepository;
    private LoggerInterface $logger;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TeamRepositoryInterface $teamRepository,
        TeamLogoRepository $teamLogoRepository,
        LoggerInterface $logger
    ) {
      parent::__construct($responseFactory);
      $this->teamRepository = $teamRepository;
      $this->teamLogoRepository = $teamLogoRepository;
      $this->logger = $logger;
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $team = $this->findTeam($request->getQueryParams());

        if ($team->getLogoId() === null) {
            return $this->buildResponse(404);
        }

        $location = $this->teamLogoRepository->generatePublicPath($team->getLogoId());

        return $this->buildRedirectResponse($location);
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $this->assertIsAdmin($request);

        $team = $this->findTeam($request->getQueryParams());

        if ($team->getLogoId() !== null) {
            $this->teamLogoRepository->delete($team->getLogoId());
            $team->setLogoId(null);
            $this->teamRepository->save($team);
            $this->teamRepository->flush();
            $this->logger->info('Team logo has been deleted', [
                'teamId' => $team->getId()
            ]);
        }

        return $this->buildResponse();
    }

    public function post(ServerRequestInterface $request): ResponseInterface
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

        return $this->buildResponse(201)->withHeader('Location', $publicPath);
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
        $maxFileSize = $this->parseByteSize(ini_get('upload_max_filesize'));
        $fileSize = (int)$uploadedFile->getSize();

        if ($fileSize > $maxFileSize || $uploadedFile->getError() === UPLOAD_ERR_INI_SIZE) {
            throw new InvalidInputException("Invalid file upload: Exceeds max size of $maxFileSize bytes");
        }

        if ($fileSize === 0) {
            throw new InvalidInputException("Invalid file upload: File is empty");
        }

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new InternalException("Invalid file upload: Code " . $uploadedFile->getError());
        }

        $mediaType = $uploadedFile->getClientMediaType();
        if ($mediaType !== 'image/webp') {
            throw new InvalidInputException("Invalid media type. Expected: image/webp. Got: $mediaType");
        }
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
