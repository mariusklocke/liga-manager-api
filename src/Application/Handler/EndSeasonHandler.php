<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\EndSeasonCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;

class EndSeasonHandler
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
     * @param EndSeasonCommand $command
     */
    public function __invoke(EndSeasonCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->end();
    }
}
