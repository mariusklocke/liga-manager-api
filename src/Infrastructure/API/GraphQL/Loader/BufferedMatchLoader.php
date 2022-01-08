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
     * @return array
     */
    public function getByMatchDay(string $matchDayId): array
    {
        $matchDayIds = array_keys($this->byMatchDayId, null, true);

        if (count($matchDayIds)) {
            $filter = new EqualityFilter(
                $this->matchRepository->getField('match_day_id'),
                Filter::MODE_INCLUDE,
                $matchDayIds
            );

            $matches = $this->matchRepository->findMany([$filter], [], null, 'match_day_id');

            foreach ($matchDayIds as $id) {
                $this->byMatchDayId[$id] = $matches[$id] ?? [];
            }
        }

        return $this->byMatchDayId[$matchDayId];
    }
}
