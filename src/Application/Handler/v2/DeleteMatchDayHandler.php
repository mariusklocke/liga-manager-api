<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\DeleteMatchDayCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;

use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchDay;

class DeleteMatchDayHandler implements AuthAwareHandler
{
    /** @var MatchDayRepositoryInterface */
    private MatchDayRepositoryInterface $matchDayRepository;

    /**
     * @param MatchDayRepositoryInterface $matchDayRepository
     */
    public function __construct(MatchDayRepositoryInterface $matchDayRepository)
    {
        $this->matchDayRepository = $matchDayRepository;
    }

    /**
     * @param DeleteMatchDayCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(DeleteMatchDayCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var MatchDay $matchDay */
        $matchDay = $this->matchDayRepository->find($command->getId());
        $matchDay->assertDeletable();

        $this->matchDayRepository->delete($matchDay);

        return [];
    }
}
