<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSingleMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\MatchFactory;

class CreateSingleMatchHandler
{
    /** @var MatchFactory */
    private $matchFactory;

    /** @var MatchRepositoryInterface */
    private $matchRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /**
     * @param MatchFactory $matchFactory
     * @param MatchRepositoryInterface $matchRepository
     * @param TeamRepositoryInterface $teamRepository
     * @param SeasonRepositoryInterface $seasonRepository
     */
    public function __construct(MatchFactory $matchFactory, MatchRepositoryInterface $matchRepository, TeamRepositoryInterface $teamRepository, SeasonRepositoryInterface $seasonRepository)
    {
        $this->matchFactory = $matchFactory;
        $this->matchRepository = $matchRepository;
        $this->teamRepository = $teamRepository;
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @param CreateSingleMatchCommand $command
     * @throws NotFoundException
     */
    public function __invoke(CreateSingleMatchCommand $command)
    {
        $season = $this->seasonRepository->find($command->getSeasonId());
        $homeTeam = $this->teamRepository->find($command->getHomeTeamId());
        $guestTeam = $this->teamRepository->find($command->getGuestTeamId());

        $match = $this->matchFactory->createMatch($season, $command->getMatchDay(), $homeTeam, $guestTeam);
        $season->addMatch($match);
        $this->matchRepository->save($match);
    }
}
