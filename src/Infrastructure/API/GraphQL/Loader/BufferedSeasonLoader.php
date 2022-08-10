<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;

class BufferedSeasonLoader
{
    private SeasonRepository $seasonRepository;

    private array $bySeasonId = [];

    public function __construct(SeasonRepository $seasonRepository)
    {
        $this->seasonRepository = $seasonRepository;
    }

    public function addSeasonId(string $seasonId): void
    {
        $this->bySeasonId[$seasonId] = null;
    }

    public function getBySeason(string $seasonId): ?array
    {
        $seasonIds = array_keys($this->bySeasonId, null, true);

        if (count($seasonIds)) {
            $filter = new EqualityFilter(
                'id',
                Filter::MODE_INCLUDE,
                $seasonIds
            );

            foreach ($this->seasonRepository->findMany([$filter]) as $season) {
                $this->bySeasonId[$season['id']] = $season;
            }
        }

        return $this->bySeasonId[$seasonId] ?? null;
    }
}
