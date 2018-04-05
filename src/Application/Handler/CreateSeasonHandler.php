<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Factory\SeasonFactory;
use HexagonalPlayground\Application\OrmRepositoryInterface;

class CreateSeasonHandler
{
    /** @var SeasonFactory */
    private $seasonFactory;

    /** @var OrmRepositoryInterface */
    private $seasonRepository;

    /**
     * @param SeasonFactory $seasonFactory
     * @param OrmRepositoryInterface $seasonRepository
     */
    public function __construct(SeasonFactory $seasonFactory, OrmRepositoryInterface $seasonRepository)
    {
        $this->seasonFactory = $seasonFactory;
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @param CreateSeasonCommand $command
     * @return string
     */
    public function handle(CreateSeasonCommand $command)
    {
        $season = $this->seasonFactory->createSeason($command->getName());
        $this->seasonRepository->save($season);
        return $season->getId();
    }
}