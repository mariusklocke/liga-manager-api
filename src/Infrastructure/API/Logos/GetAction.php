<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAction implements ActionInterface
{
    use TeamLogoTrait;

    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $team = $this->findTeam($request->getQueryParams());

        if ($team->getLogoId() === null) {
            return $response->withStatus(404);
        }

        return $response
            ->withStatus(302)
            ->withHeader('Location', $this->buildPublicPath($team->getLogoId()));
    }
}
