<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Season;

class StartSeasonHandler
{
    /** @var OrmRepositoryInterface */
    private $seasonRepository;

    /**
     * @param OrmRepositoryInterface $seasonRepository
     */
    public function __construct(OrmRepositoryInterface $seasonRepository)
    {
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @param StartSeasonCommand $command
     */
    public function __invoke(StartSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->start();
        $this->seasonRepository->save($season);
    }
}