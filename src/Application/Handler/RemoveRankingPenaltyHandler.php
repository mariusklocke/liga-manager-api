<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RemoveRankingPenaltyCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;

class RemoveRankingPenaltyHandler
{
    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /**
     * @param SeasonRepositoryInterface $seasonRepository
     */
    public function __construct(SeasonRepositoryInterface $seasonRepository)
    {
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @param RemoveRankingPenaltyCommand $command
     */
    public function __invoke(RemoveRankingPenaltyCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->getRanking()->removePenalty($command->getRankingPenaltyId(), $command->getAuthenticatedUser());
    }
}