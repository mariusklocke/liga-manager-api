<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSeasonCommand;
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
     * @return string
     */
    public function __invoke(CreateSeasonCommand $command)
    {
        $season = new Season($command->getName());
        $this->seasonRepository->save($season);
        return $season->getId();
    }
}