<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Domain\MatchFactory;

class CreateMatchesForSeasonHandler
{
    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /**
     * @param SeasonRepositoryInterface $seasonRepository
     */
    public function __construct(SeasonRepositoryInterface $seasonRepository)
    {
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @param CreateMatchesForSeasonCommand $command
     * @throws NotFoundException
     */
    public function __invoke(CreateMatchesForSeasonCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->clearMatchDays();
        (new MatchFactory())->createMatchDaysForSeason($season, $command->getMatchDaysDates());
        $this->seasonRepository->save($season);
    }
}
