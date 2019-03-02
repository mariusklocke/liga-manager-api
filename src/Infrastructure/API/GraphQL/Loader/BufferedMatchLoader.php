<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

class BufferedMatchLoader
{
    /** @var MatchLoader */
    private $matchLoader;

    /** @var array */
    private $byMatchDayId = [];

    /**
     * @param MatchLoader $matchLoader
     */
    public function __construct(MatchLoader $matchLoader)
    {
        $this->matchLoader = $matchLoader;
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
        foreach ($this->matchLoader->loadByMatchDayId($matchDayIds) as $id => $matches) {
            $this->byMatchDayId[$id] = $matches;
        }
        return $this->byMatchDayId[$matchDayId] ?? null;
    }
}