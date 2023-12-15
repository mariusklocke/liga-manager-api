<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\Security\AuthorizationTrait;
use HexagonalPlayground\Infrastructure\Filesystem\TeamLogoRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class DeleteAction implements ActionInterface
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

        if ($team->getLogoId() !== null) {
            $this->teamLogoRepository->delete($team->getLogoId());
            $team->setLogoId(null);
            $this->teamRepository->save($team);
            $this->teamRepository->flush();
            $this->logger->info('Team logo has been deleted', [
                'teamId' => $team->getId()
            ]);
        }

        return $response->withStatus(204);
    }
}
