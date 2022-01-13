<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RescheduleMatchDayCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchDay;

class RescheduleMatchDayHandler implements AuthAwareHandler
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
     * @return array|Event[]
     */
    public function __invoke(RescheduleMatchDayCommand $command, AuthContext $authContext): array
    {
        $events = [];

        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var MatchDay $matchDay */
        $matchDay = $this->matchDayRepository->find($command->getMatchDayId());
        $matchDay->reschedule($command->getDatePeriod()->getStartDate(), $command->getDatePeriod()->getEndDate());
        $this->matchDayRepository->save($matchDay);

        $events[] = new Event('match:day:rescheduled', [
            'matchDayId' => $matchDay->getId(),
            'datePeriod' => [
                'from' => $matchDay->getStartDate()->format(Event::DATE_FORMAT),
                'to'   => $matchDay->getEndDate()->format(Event::DATE_FORMAT)
            ]
        ]);

        return $events;
    }
}
