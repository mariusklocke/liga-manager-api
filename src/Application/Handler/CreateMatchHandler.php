<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateMatchCommand;
use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchDay;
use HexagonalPlayground\Domain\Team;

class CreateMatchHandler implements AuthAwareHandler
{
    /** @var MatchDayRepositoryInterface */
    private MatchDayRepositoryInterface $matchDayRepository;

    /** @var TeamRepositoryInterface */
    private TeamRepositoryInterface $teamRepository;

    /**
     * @param MatchDayRepositoryInterface $matchDayRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(MatchDayRepositoryInterface $matchDayRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->matchDayRepository = $matchDayRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param CreateMatchCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(CreateMatchCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var MatchDay $matchDay */
        $matchDay = $this->matchDayRepository->find($command->getMatchDayId());
        /** @var Team $homeTeam */
        $homeTeam = $this->teamRepository->find($command->getHomeTeamId());
        /** @var Team $guestTeam */
        $guestTeam = $this->teamRepository->find($command->getGuestTeamId());
        $matchDay->createMatch($command->getId(), $homeTeam, $guestTeam);

        $this->matchDayRepository->save($matchDay);

        return [];
    }
}
