<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\AddRankingPenaltyCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;

class AddRankingPenaltyHandler implements AuthAwareHandler
{
    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /**
     * @param SeasonRepositoryInterface $seasonRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(SeasonRepositoryInterface $seasonRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->seasonRepository = $seasonRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param AddRankingPenaltyCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(AddRankingPenaltyCommand $command, AuthContext $authContext): void
    {
        IsAdmin::check($authContext->getUser());

        $season = $this->seasonRepository->find($command->getSeasonId());
        $team   = $this->teamRepository->find($command->getTeamId());

        $season->getRanking()->addPenalty(
            $command->getId(),
            $team,
            $command->getReason(),
            $command->getPoints(),
            $authContext->getUser()
        );

        $this->seasonRepository->save($season);
    }
}