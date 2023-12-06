<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class DeleteAction implements ActionInterface
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

        if ($team->getLogoId() !== null) {
            $this->deleteLogo($team->getLogoId());
            $team->setLogoId(null);
            $this->teamRepository->save($team);
            $this->logger->info('Team logo has been deleted', [
                'teamId' => $team->getId()
            ]);
        }

        return $response->withStatus(204);
    }
}
