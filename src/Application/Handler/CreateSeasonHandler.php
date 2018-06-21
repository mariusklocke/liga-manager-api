<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Season;

class CreateSeasonHandler
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
     * @param CreateSeasonCommand $command
     * @return string
     */
    public function __invoke(CreateSeasonCommand $command)
    {
        $season = new Season($command->getName());
        $this->seasonRepository->save($season);
        return $season->getId();
    }
}