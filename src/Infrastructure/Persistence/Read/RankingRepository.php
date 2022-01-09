<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\Field;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class RankingRepository extends AbstractRepository
{
    /** @var Field[] */
    private $positionFields;

    /** @var Hydrator */
    private $positionHydrator;

    /** @var Field[] */
    private $penaltyFields;

    /** @var Hydrator */
    private $penaltyHydrator;

    public function __construct(ReadDbGatewayInterface $gateway)
    {
        parent::__construct($gateway);

        $this->positionFields = [
            new StringField('season_id', false),
            new StringField('team_id', false),
            new StringField('sort_index', false),
            new IntegerField('number', false),
            new IntegerField('matches', false),
            new IntegerField('wins', false),
            new IntegerField('draws', false),
            new IntegerField('losses', false),
            new IntegerField('scored_goals', false),
            new IntegerField('conceded_goals', false),
            new IntegerField('points', false)
        ];

        $this->positionHydrator = new Hydrator($this->positionFields);

        $this->penaltyFields = [
            new StringField('id', false),
            new StringField('season_id', false),
            new StringField('team_id', false),
            new StringField('reason', false),
            new IntegerField('points', false),
            new DateTimeField('created_at', false)
        ];

        $this->penaltyHydrator = new Hydrator($this->penaltyFields);
    }

    protected function getTableName(): string
    {
        return 'rankings';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            new StringField('season_id', false),
            new DateTimeField('updated_at', true)
        ];
    }

    protected function getPositionField(string $name): ?Field
    {
        foreach ($this->positionFields as $fieldDefinition) {
            if ($fieldDefinition->getName() === $name) {
                return $fieldDefinition;
            }
        }

        return null;
    }

    protected function getPenaltyField(string $name): ?Field
    {
        foreach ($this->penaltyFields as $fieldDefinition) {
            if ($fieldDefinition->getName() === $name) {
                return $fieldDefinition;
            }
        }

        return null;
    }

    /**
     * @param string $seasonId
     * @return array|null
     */
    public function findRanking(string $seasonId): ?array
    {
        $result = $this->gateway->fetch(
            $this->getTableName(),
            [],
            [new EqualityFilter($this->getField('season_id'), Filter::MODE_INCLUDE, [$seasonId])]
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
            [new EqualityFilter($this->getPositionField('season_id'), Filter::MODE_INCLUDE, [$seasonId])],
            [new Sorting($this->getPositionField('sort_index'), Sorting::DIRECTION_ASCENDING)]
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
            [new EqualityFilter($this->getPenaltyField('season_id'), Filter::MODE_INCLUDE, [$seasonId])],
            [new Sorting($this->getPenaltyField('created_at'), Sorting::DIRECTION_ASCENDING)]
        );

        return $this->penaltyHydrator->hydrateMany($result);
    }
}
