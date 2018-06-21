<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSingleMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\MatchFactory;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class CreateSingleMatchHandler
{
    /** @var MatchFactory */
    private $matchFactory;

    /** @var OrmRepositoryInterface */
    private $matchRepository;

    /** @var OrmRepositoryInterface */
    private $teamRepository;

    /** @var OrmRepositoryInterface */
    private $seasonRepository;

    /**
     * @param MatchFactory $matchFactory
     * @param OrmRepositoryInterface $matchRepository
     * @param OrmRepositoryInterface $teamRepository
     * @param OrmRepositoryInterface $seasonRepository
     */
    public function __construct(MatchFactory $matchFactory, OrmRepositoryInterface $matchRepository, OrmRepositoryInterface $teamRepository, OrmRepositoryInterface $seasonRepository)
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
        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        /** @var Team $homeTeam */
        $homeTeam = $this->teamRepository->find($command->getHomeTeamId());
        /** @var Team $guestTeam */
        $guestTeam = $this->teamRepository->find($command->getGuestTeamId());

        $match = $this->matchFactory->createMatch($season, $command->getMatchDay(), $homeTeam, $guestTeam);
        $season->addMatch($match);
        $this->matchRepository->save($match);
    }
}
