<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ReplaceTeamInSeasonCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class ReplaceTeamInSeasonHandler implements AuthAwareHandler
{
    /** @var TeamRepositoryInterface */
    private $teamRepository;
    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    public function __construct(TeamRepositoryInterface $teamRepository, SeasonRepositoryInterface $seasonRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @param ReplaceTeamInSeasonCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(ReplaceTeamInSeasonCommand $command, AuthContext $authContext): void
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var Team $currentTeam */
        $currentTeam = $this->teamRepository->find($command->getCurrentTeamId());
        /** @var Team $replacementTeam */
        $replacementTeam = $this->teamRepository->find($command->getReplacementTeamId());
        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());

        $season->replaceTeam($currentTeam, $replacementTeam);

        $this->seasonRepository->save($season);
    }
}
