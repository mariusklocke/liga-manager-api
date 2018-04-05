<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Season;

class DeleteSeasonHandler
{
    /** @var OrmRepositoryInterface */
    private $seasonRepository;

    /**
     * DeleteSeasonHandler constructor.
     * @param OrmRepositoryInterface $seasonRepository
     */
    public function __construct(OrmRepositoryInterface $seasonRepository)
    {
        $this->seasonRepository = $seasonRepository;
    }

    public function handle(DeleteSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->clearMatches()->clearTeams();
        $this->seasonRepository->delete($season);
    }
}
