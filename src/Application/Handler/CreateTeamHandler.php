<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\IdGeneratorInterface;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Team;

class CreateTeamHandler
{
    /** @var OrmRepositoryInterface */
    private $teamRepository;
    /** @var IdGeneratorInterface */
    private $idGenerator;

    /**
     * @param OrmRepositoryInterface $teamRepository
     * @param IdGeneratorInterface $idGenerator
     */
    public function __construct(OrmRepositoryInterface $teamRepository, IdGeneratorInterface $idGenerator)
    {
        $this->teamRepository = $teamRepository;
        $this->idGenerator = $idGenerator;
    }

    /**
     * @param CreateTeamCommand $command
     * @return string Created team's ID
     */
    public function handle(CreateTeamCommand $command)
    {
        $team = new Team($command->getTeamName());
        $this->teamRepository->save($team);
        return $team->getId();
    }
}
