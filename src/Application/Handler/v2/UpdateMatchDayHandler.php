<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\UpdateMatchDayCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;

use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\MatchDay;

class UpdateMatchDayHandler implements AuthAwareHandler
{
    private MatchDayRepositoryInterface $matchDayRepository;

    /**
     * @param MatchDayRepositoryInterface $matchDayRepository
     */
    public function __construct(MatchDayRepositoryInterface $matchDayRepository)
    {
        $this->matchDayRepository = $matchDayRepository;
    }

    public function __invoke(UpdateMatchDayCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var MatchDay $matchDay */
        $matchDay = $this->matchDayRepository->find($command->getId());
        $matchDay->reschedule($command->getDatePeriod()->getStartDate(), $command->getDatePeriod()->getEndDate());

        $this->matchDayRepository->save($matchDay);

        return [];
    }
}
