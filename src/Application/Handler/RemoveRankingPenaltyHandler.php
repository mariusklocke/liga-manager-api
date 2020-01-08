<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RemoveRankingPenaltyCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Season;

class RemoveRankingPenaltyHandler implements AuthAwareHandler
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
     * @param AuthContext $authContext
     */
    public function __invoke(RemoveRankingPenaltyCommand $command, AuthContext $authContext)
    {
        IsAdmin::check($authContext->getUser());

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->getRanking()->removePenalty($command->getRankingPenaltyId(), $authContext->getUser());
    }
}