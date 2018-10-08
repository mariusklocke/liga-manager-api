<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RescheduleMatchDayCommand;
use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;

class RescheduleMatchDayHandler
{
    /** @var MatchDayRepositoryInterface */
    private $matchDayRepository;

    /**
     * @param MatchDayRepositoryInterface $matchDayRepository
     */
    public function __construct(MatchDayRepositoryInterface $matchDayRepository)
    {
        $this->matchDayRepository = $matchDayRepository;
    }

    public function __invoke(RescheduleMatchDayCommand $command)
    {
        $matchDay = $this->matchDayRepository->find($command->getMatchDayId());
        $matchDay->reschedule($command->getDatePeriod()->getStartDate(), $command->getDatePeriod()->getEndDate());
        $this->matchDayRepository->save($matchDay);
    }
}