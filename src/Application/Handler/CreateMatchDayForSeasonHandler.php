<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateMatchDayForSeasonCommand;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Season;

class CreateMatchDayForSeasonHandler implements AuthAwareHandler
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
     * @param CreateMatchDayForSeasonCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(CreateMatchDayForSeasonCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->createMatchDay(
            $command->getId(),
            $command->getNumber(),
            $command->getDatePeriod()->getStartDate(),
            $command->getDatePeriod()->getEndDate()
        );
        $this->seasonRepository->save($season);

        return [];
    }
}
