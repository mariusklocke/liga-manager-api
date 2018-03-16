<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSingleMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Factory\MatchFactory;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class CreateSingleMatchHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /** @var MatchFactory */
    private $matchFactory;

    /**
     * @param ObjectPersistenceInterface $persistence
     * @param MatchFactory $matchFactory
     */
    public function __construct(ObjectPersistenceInterface $persistence, MatchFactory $matchFactory)
    {
        $this->persistence  = $persistence;
        $this->matchFactory = $matchFactory;
    }

    /**
     * @param CreateSingleMatchCommand $command
     * @throws NotFoundException
     */
    public function handle(CreateSingleMatchCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        /** @var Team $homeTeam */
        $homeTeam = $this->persistence->find(Team::class, $command->getHomeTeamId());
        /** @var Team $guestTeam */
        $guestTeam = $this->persistence->find(Team::class, $command->getGuestTeamId());

        $match = $this->matchFactory->createMatch($season, $command->getMatchDay(), $homeTeam, $guestTeam);
        $season->addMatch($match);
        $this->persistence->persist($match);
    }
}
