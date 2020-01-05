<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\MatchFactory;

class CreateMatchesForSeasonHandler implements AuthAwareHandler
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
     * @param AuthContext $authContext
     * @throws NotFoundException
     */
    public function __invoke(CreateMatchesForSeasonCommand $command, AuthContext $authContext)
    {
        IsAdmin::check($authContext->getUser());
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->clearMatchDays();
        (new MatchFactory())->createMatchDaysForSeason($season, $command->getMatchDaysDates());
        $this->seasonRepository->save($season);
    }
}
