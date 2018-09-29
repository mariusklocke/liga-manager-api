<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;

class DeleteSeasonHandler
{
    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /**
     * DeleteSeasonHandler constructor.
     * @param SeasonRepositoryInterface $seasonRepository
     */
    public function __construct(SeasonRepositoryInterface $seasonRepository)
    {
        $this->seasonRepository = $seasonRepository;
    }

    public function __invoke(DeleteSeasonCommand $command)
    {
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->clearMatchDays();
        $season->clearTeams();
        $this->seasonRepository->delete($season);
    }
}
