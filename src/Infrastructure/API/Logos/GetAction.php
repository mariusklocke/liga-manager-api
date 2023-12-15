<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\Filesystem\TeamLogoRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAction implements ActionInterface
{
    use TeamFinderTrait;

    private TeamLogoRepository $teamLogoRepository;

    public function __construct(TeamRepositoryInterface $teamRepository, TeamLogoRepository $teamLogoRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->teamLogoRepository = $teamLogoRepository;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $team = $this->findTeam($request->getQueryParams());

        if ($team->getLogoId() === null) {
            return $response->withStatus(404);
        }

        $location = $this->teamLogoRepository->generatePublicPath($team->getLogoId());

        return $response->withStatus(302)->withHeader('Location', $location);
    }
}
