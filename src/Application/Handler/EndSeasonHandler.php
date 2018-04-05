<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\EndSeasonCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Season;

class EndSeasonHandler
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


    public function handle(EndSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $season->end();
    }
}
