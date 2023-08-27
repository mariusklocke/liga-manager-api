<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\CreateMatchDayCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Competition;
use HexagonalPlayground\Domain\Exception\InvalidInputException;

class CreateMatchDayHandler implements AuthAwareHandler
{
    /** @var TournamentRepositoryInterface */
    private TournamentRepositoryInterface $tournamentRepository;

    /** @var SeasonRepositoryInterface */
    private SeasonRepositoryInterface $seasonRepository;

    /**
     * @param TournamentRepositoryInterface $tournamentRepository
     * @param SeasonRepositoryInterface $seasonRepository
     */
    public function __construct(TournamentRepositoryInterface $tournamentRepository, SeasonRepositoryInterface $seasonRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
        $this->seasonRepository = $seasonRepository;
    }

    public function __invoke(CreateMatchDayCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        [$repository, $competition] = $this->findCompetition($command);

        $competition->createMatchDay(
            $command->getId(),
            $command->getNumber(),
            $command->getDatePeriod()->getStartDate(),
            $command->getDatePeriod()->getEndDate()
        );

        $repository->save($competition);

        return [];
    }

    private function findCompetition(CreateMatchDayCommand $command): array
    {
        $competition = null;
        $repository = null;

        if ($command->getTournamentId()) {
            $repository = $this->tournamentRepository;
            /** @var Competition $competition */
            $competition = $repository->find($command->getTournamentId());
        }

        if ($command->getSeasonId()) {
            $repository = $this->seasonRepository;
            /** @var Competition $competition */
            $competition = $repository->find($command->getSeasonId());
        }

        if ($competition === null || $repository === null) {
            throw new InvalidInputException('Creating a match day requires either seasonId or tournamentId');
        }

        return [$repository, $competition];
    }
}
