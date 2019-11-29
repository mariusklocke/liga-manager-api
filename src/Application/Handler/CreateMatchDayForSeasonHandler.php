<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateMatchDayForSeasonCommand;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;

class CreateMatchDayForSeasonHandler
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
     * @param CreateMatchDayForSeasonCommand $command
     */
    public function __invoke(CreateMatchDayForSeasonCommand $command)
    {
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->createMatchDay(
            $command->getId(),
            $command->getNumber(),
            $command->getDatePeriod()->getStartDate(),
            $command->getDatePeriod()->getEndDate()
        );
        $this->seasonRepository->save($season);
    }
}