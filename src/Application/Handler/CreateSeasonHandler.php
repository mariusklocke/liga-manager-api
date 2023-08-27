<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Season;

class CreateSeasonHandler implements AuthAwareHandler
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
     * @param CreateSeasonCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(CreateSeasonCommand $command, AuthContext $authContext): array
    {
        $events = [];

        $authContext->getUser()->assertIsAdmin();

        $season = new Season($command->getId(), $command->getName());
        $this->seasonRepository->save($season);

        $events[] = new Event('season:created', [
            'seasonId' => $season->getId()
        ]);

        return $events;
    }
}
