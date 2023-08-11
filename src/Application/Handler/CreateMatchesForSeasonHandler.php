<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchDayGenerator;
use HexagonalPlayground\Domain\Season;

class CreateMatchesForSeasonHandler implements AuthAwareHandler
{
    /** @var SeasonRepositoryInterface */
    private SeasonRepositoryInterface $seasonRepository;

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
     * @return array|Event[]
     * @throws NotFoundException
     */
    public function __invoke(CreateMatchesForSeasonCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->clearMatchDays();

        $generator = new MatchDayGenerator();
        $generator->generateMatchDaysForSeason($season, $command->getMatchDaysDates());

        $this->seasonRepository->save($season);

        return [];
    }
}
