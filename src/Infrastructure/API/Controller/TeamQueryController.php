<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use Psr\Http\Message\ResponseInterface;

class TeamQueryController
{
    use ResponseFactoryTrait;

    /** @var TeamRepository */
    private $repository;

    public function __construct(TeamRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return ResponseInterface
     */
    public function findAllTeams(): ResponseInterface
    {
        return $this->createResponse(200, $this->repository->findAllTeams());
    }

    /**
     * @param string $teamId
     * @return ResponseInterface
     */
    public function findTeamById(string $teamId): ResponseInterface
    {
        return $this->createResponse(200, $this->repository->findTeamById($teamId));
    }

    /**
     * @param string $seasonId
     * @return ResponseInterface
     */
    public function findTeamsBySeasonId(string $seasonId): ResponseInterface
    {
        return $this->createResponse(200, $this->repository->findTeamsBySeasonId($seasonId));
    }
}