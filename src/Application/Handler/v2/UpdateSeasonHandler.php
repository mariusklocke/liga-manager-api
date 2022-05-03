<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\UpdateSeasonCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Util\Assert;

class UpdateSeasonHandler implements AuthAwareHandler
{
    private SeasonRepositoryInterface $seasonRepository;

    private TeamRepositoryInterface $teamRepository;

    /**
     * @param SeasonRepositoryInterface $seasonRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(SeasonRepositoryInterface $seasonRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->seasonRepository = $seasonRepository;
        $this->teamRepository = $teamRepository;
    }

    public function __invoke(UpdateSeasonCommand $command, AuthContext $authContext): array
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getId());

        $addTeamIds = array_diff($command->getTeamIds(), $season->getTeamIds());
        $removeTeamIds = array_diff($season->getTeamIds(), $command->getTeamIds());

        if ($season->isInProgress()) {
            Assert::true(
                count($addTeamIds) === count($removeTeamIds),
                'Team count must not change when season in progress'
            );

            $teamSwaps = array_combine(array_values($removeTeamIds), array_values($addTeamIds));
            foreach ($teamSwaps as $removeTeamId => $addTeamId) {
                $season->replaceTeam($this->findTeam($removeTeamId), $this->findTeam($addTeamId));
            }
        } else {
            foreach ($addTeamIds as $addTeamId) {
                $season->addTeam($this->findTeam($addTeamId));
            }

            foreach ($removeTeamIds as $removeTeamId) {
                $season->removeTeam($this->findTeam($removeTeamId));
            }
        }

        $season->setName($command->getName());
        $season->setState($command->getState());

        $this->seasonRepository->save($season);

        return [];
    }

    private function findTeam(string $id): Team
    {
        return $this->teamRepository->find($id);
    }
}
