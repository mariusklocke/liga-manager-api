<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Util\Uuid;

class SeasonMapper
{
    /** @var SeasonRepositoryInterface */
    private $repository;

    /**
     * @param SeasonRepositoryInterface $repository
     */
    public function __construct(SeasonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param L98SeasonModel $l98Season
     * @return Season
     */
    public function create(L98SeasonModel $l98Season): Season
    {
        $season = new Season(Uuid::create(), $l98Season->getName());
        $this->repository->save($season);
        return $season;
    }
}