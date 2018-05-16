<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\MatchFactory;
use HexagonalPlayground\Domain\Season;

class CreateMatchesForSeasonHandler
{
    /** @var MatchFactory */
    private $matchFactory;
    /** @var OrmRepositoryInterface */
    private $seasonRepository;
    /** @var OrmRepositoryInterface */
    private $matchRepository;

    /**
     * @param MatchFactory $matchFactory
     * @param OrmRepositoryInterface $seasonRepository
     * @param OrmRepositoryInterface $matchRepository
     */
    public function __construct(MatchFactory $matchFactory, OrmRepositoryInterface $seasonRepository, OrmRepositoryInterface $matchRepository)
    {
        $this->matchFactory = $matchFactory;
        $this->seasonRepository = $seasonRepository;
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param CreateMatchesForSeasonCommand $command
     * @throws NotFoundException
     */
    public function handle(CreateMatchesForSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->clearMatches();
        $matches = $this->matchFactory->createMatchesForSeason($season, $command->getStartAt());
        foreach ($matches as $match) {
            $season->addMatch($match);
            $this->matchRepository->save($match);
        }
    }
}
