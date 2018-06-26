<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Domain\MatchFactory;

class CreateMatchesForSeasonHandler
{
    /** @var MatchFactory */
    private $matchFactory;
    /** @var SeasonRepositoryInterface */
    private $seasonRepository;
    /** @var MatchRepositoryInterface */
    private $matchRepository;

    /**
     * @param MatchFactory $matchFactory
     * @param SeasonRepositoryInterface $seasonRepository
     * @param MatchRepositoryInterface $matchRepository
     */
    public function __construct(MatchFactory $matchFactory, SeasonRepositoryInterface $seasonRepository, MatchRepositoryInterface $matchRepository)
    {
        $this->matchFactory = $matchFactory;
        $this->seasonRepository = $seasonRepository;
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param CreateMatchesForSeasonCommand $command
     * @throws NotFoundException
     */
    public function __invoke(CreateMatchesForSeasonCommand $command)
    {
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->clearMatches();
        $matches = $this->matchFactory->createMatchesForSeason($season, $command->getStartAt());
        foreach ($matches as $match) {
            $season->addMatch($match);
            $this->matchRepository->save($match);
        }
    }
}
