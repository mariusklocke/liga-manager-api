<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Season;

class CreateSeasonHandler implements AuthAwareHandler
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
     * @param CreateSeasonCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(CreateSeasonCommand $command, AuthContext $authContext): void
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();
        $season = new Season($command->getId(), $command->getName());
        $this->seasonRepository->save($season);
    }
}