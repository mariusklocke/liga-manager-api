<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;

class RankingRepository extends AbstractRepository
{
    /** @var Hydrator */
    private $positionHydrator;

    /** @var Hydrator */
    private $penaltyHydrator;

    public function __construct(ReadDbGatewayInterface $gateway)
    {
        parent::__construct($gateway);

        $this->positionHydrator = new Hydrator([
            'season_id' => Hydrator::TYPE_STRING,
            'team_id' => Hydrator::TYPE_STRING,
            'sort_index' => Hydrator::TYPE_INT,
            'number' => Hydrator::TYPE_INT,
            'matches' => Hydrator::TYPE_INT,
            'wins' => Hydrator::TYPE_INT,
            'draws' => Hydrator::TYPE_INT,
            'losses' => Hydrator::TYPE_INT,
            'scored_goals' => Hydrator::TYPE_INT,
            'conceded_goals' => Hydrator::TYPE_INT,
            'points' => Hydrator::TYPE_INT
        ]);

        $this->penaltyHydrator = new Hydrator([
            'id' => Hydrator::TYPE_STRING,
            'season_id' => Hydrator::TYPE_STRING,
            'team_id' => Hydrator::TYPE_STRING,
            'reason' => Hydrator::TYPE_STRING,
            'points' => Hydrator::TYPE_INT,
            'created_at' => Hydrator::TYPE_DATETIME
        ]);
    }

    protected function getTableName(): string
    {
        return 'rankings';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            'season_id' => Hydrator::TYPE_STRING,
            'updated_at' => Hydrator::TYPE_DATETIME
        ];
    }

    /**
     * @param string $seasonId
     * @return array|null
     */
    public function findRanking(string $seasonId): ?array
    {
        $result = $this->gateway->fetch(
            'rankings',
            [],
            [new EqualityFilter('season_id', Filter::MODE_INCLUDE, [$seasonId])]
        );

        return $this->hydrator->hydrateOne($result);
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findRankingPositions(string $seasonId): array
    {
        $result = $this->gateway->fetch(
            'ranking_positions',
            [],
            [new EqualityFilter('season_id', Filter::MODE_INCLUDE, [$seasonId])],
            [new Sorting('sort_index', Sorting::DIRECTION_ASCENDING)]
        );

        return $this->positionHydrator->hydrateMany($result);
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findRankingPenalties(string $seasonId): array
    {
        $result = $this->gateway->fetch(
            'ranking_penalties',
            [],
            [new EqualityFilter('season_id', Filter::MODE_INCLUDE, [$seasonId])],
            [new Sorting('created_at', Sorting::DIRECTION_ASCENDING)]
        );

        return $this->penaltyHydrator->hydrateMany($result);
    }
}
