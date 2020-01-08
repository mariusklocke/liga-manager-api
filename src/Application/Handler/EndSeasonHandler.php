<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\EndSeasonCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Season;

class EndSeasonHandler implements AuthAwareHandler
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
     * @param AuthContext $authContext
     */
    public function __invoke(EndSeasonCommand $command, AuthContext $authContext)
    {
        IsAdmin::check($authContext->getUser());

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->end();
    }
}
