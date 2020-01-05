<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RescheduleMatchDayCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;

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

    /**
     * @param RescheduleMatchDayCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(RescheduleMatchDayCommand $command, AuthContext $authContext)
    {
        IsAdmin::check($authContext->getUser());
        $matchDay = $this->matchDayRepository->find($command->getMatchDayId());
        $matchDay->reschedule($command->getDatePeriod()->getStartDate(), $command->getDatePeriod()->getEndDate());
        $this->matchDayRepository->save($matchDay);
    }
}