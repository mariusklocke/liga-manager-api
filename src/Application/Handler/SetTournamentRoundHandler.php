<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Tournament;
use HexagonalPlayground\Domain\Util\Assert;

class SetTournamentRoundHandler implements AuthAwareHandler
{
    /** @var TournamentRepositoryInterface */
    private TournamentRepositoryInterface $tournamentRepository;

    /** @var TeamRepositoryInterface */
    private TeamRepositoryInterface $teamRepository;

    /**
     * @param TournamentRepositoryInterface $tournamentRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(TournamentRepositoryInterface $tournamentRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param SetTournamentRoundCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(SetTournamentRoundCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        Assert::true(
            count($command->getTeamIdPairs()) > 0,
            'Team pairs cannot be empty',
            InvalidInputException::class
        );

        Assert::true(
            count($command->getTeamIdPairs()) <= 64,
            'Request exceeds maximum amount of 64 team pairs',
            InvalidInputException::class
        );

        /** @var Tournament $tournament */
        $tournament = $this->tournamentRepository->find($command->getTournamentId());
        $tournament->clearMatchesForRound($command->getRound());
        $tournament->removeMatchDay($command->getRound());
        $round = $tournament->createMatchDay(
            null,
            $command->getRound(),
            $command->getDatePeriod()->getStartDate(),
            $command->getDatePeriod()->getEndDate()
        );
        foreach ($command->getTeamIdPairs() as $pair) {
            /** @var Team $homeTeam */
            $homeTeam = $this->teamRepository->find($pair->getHomeTeamId());
            /** @var Team $guestTeam */
            $guestTeam = $this->teamRepository->find($pair->getGuestTeamId());

            $round->createMatch(null, $homeTeam, $guestTeam);
        }
        $this->tournamentRepository->save($tournament);

        return [];
    }
}
