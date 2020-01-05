<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;

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

    /**
     * @param DeleteSeasonCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(DeleteSeasonCommand $command, AuthContext $authContext)
    {
        IsAdmin::check($authContext->getUser());
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->clearMatchDays();
        $season->clearTeams();
        $this->seasonRepository->delete($season);
    }
}
