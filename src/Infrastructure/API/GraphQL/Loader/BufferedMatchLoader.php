<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

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
        foreach ($this->matchRepository->findMatchesByMatchDayIds($matchDayIds) as $id => $matches) {
            $this->byMatchDayId[$id] = $matches;
        }
        return $this->byMatchDayId[$matchDayId] ?? null;
    }
}
