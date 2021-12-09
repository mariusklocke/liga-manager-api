<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;

class BufferedMatchLoader
{
    /** @var MatchRepository */
    private $matchRepository;

    /** @var array */
    private $byMatchDayId = [];

    /**
     * @param MatchRepository $matchRepository
     */
    public function __construct(MatchRepository $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param string $matchDayId
     */
    public function addMatchDay(string $matchDayId)
    {
        $this->byMatchDayId[$matchDayId] = null;
    }

    /**
     * @param string $matchDayId
     * @return array|null
     */
    public function getByMatchDay(string $matchDayId): ?array
    {
        $matchDayIds = array_keys($this->byMatchDayId, null, true);

        if (count($matchDayIds)) {
            $filters = [new EqualityFilter('match_day_id', Filter::MODE_INCLUDE, $matchDayIds)];

            foreach ($this->matchRepository->findMany($filters) as $match) {
                $this->byMatchDayId[$match['match_day_id']][] = $match;
            }
        }

        return $this->byMatchDayId[$matchDayId] ?? null;
    }
}
