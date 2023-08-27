<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\CreateSeasonCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class CreateSeasonHandler implements AuthAwareHandler
{
    /** @var SeasonRepositoryInterface */
    private SeasonRepositoryInterface $seasonRepository;

    /** @var TeamRepositoryInterface */
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

    /**
     * @param CreateSeasonCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(CreateSeasonCommand $command, AuthContext $authContext): array
    {
        $events = [];

        $authContext->getUser()->assertIsAdmin();

        $season = new Season($command->getId(), $command->getName());

        foreach ($command->getTeamIds() as $teamId) {
            /** @var Team $team */
            $team = $this->teamRepository->find($teamId);
            $season->addTeam($team);
        }

        $this->seasonRepository->save($season);

        $events[] = new Event('season:created', [
            'seasonId' => $season->getId()
        ]);

        return $events;
    }
}
