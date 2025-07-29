<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Domain\Team;
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
    private TeamRepositoryInterface $teamRepository;
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

        $team->getLogoId() !== null || throw new NotFoundException('teamLogoNotFound', [$team->getId()]);

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

        $logoId = $this->teamLogoRepository->save($file);
        $publicPath = $this->teamLogoRepository->generatePublicPath($logoId);
        if ($team->getLogoId() !== null) {
            $this->teamLogoRepository->delete($team->getLogoId());
        }
        $team->setLogoId($logoId);
        $this->teamRepository->save($team);
        $this->teamRepository->flush();

        $this->logger->info("Uploaded team logo has been saved", [
            'teamId' => $team->getId(),
            'fileId' => $team->getLogoId()
        ]);

        return $this->buildResponse(201)->withHeader('Location', $publicPath);
    }

    private function findTeam(array $queryParams): Team
    {
        TypeAssert::assertString($queryParams['teamId'], 'teamId');

        /** @var Team $team */
        $team = $this->teamRepository->find($queryParams['teamId']);

        return $team;
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
}
