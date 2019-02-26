<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Domain\Season;

class CreateSeasonHandler
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
     */
    public function __invoke(CreateSeasonCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());
        $season = new Season($command->getId(), $command->getName());
        $this->seasonRepository->save($season);
    }
}