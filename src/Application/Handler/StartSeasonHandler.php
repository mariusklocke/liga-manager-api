<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Season;

class StartSeasonHandler implements AuthAwareHandler
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
     * @param StartSeasonCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(StartSeasonCommand $command, AuthContext $authContext): array
    {
        $events = [];

        $authContext->getUser()->assertIsAdmin();

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->start();
        $this->seasonRepository->save($season);

        $events[] = new Event('season:started', [
            'seasonId' => $season->getId()
        ]);

        return $events;
    }
}
