<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Domain\Season;

class StartSeasonHandler
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
     * @param StartSeasonCommand $command
     */
    public function __invoke(StartSeasonCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());
        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->start();
        $this->seasonRepository->save($season);
    }
}