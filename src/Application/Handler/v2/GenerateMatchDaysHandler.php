<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\GenerateMatchDaysCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchDayGenerator;
use HexagonalPlayground\Domain\Season;

class GenerateMatchDaysHandler implements AuthAwareHandler
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
     * @param GenerateMatchDaysCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(GenerateMatchDaysCommand $command, AuthContext $authContext): array
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->clearMatchDays();

        $generator = new MatchDayGenerator();
        $generator->generateMatchDaysForSeason($season, $command->getMatchDaysDates());

        $this->seasonRepository->save($season);

        return [];
    }
}
