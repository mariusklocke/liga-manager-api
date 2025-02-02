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
use RuntimeException;

class RankingRepository extends AbstractRepository
{
    /** @var Field[] */
    private array $positionFields;

    /** @var Field[] */
    private array $flattenedPositionFields;

    /** @var Hydrator */
    private Hydrator $positionHydrator;

    /** @var Field[] */
    private array $penaltyFields;

    /** @var Field[] */
    private array $flattenedPenaltyFields;

    /** @var Hydrator */
    private Hydrator $penaltyHydrator;

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

        $this->flattenedPositionFields = $this->flattenFieldDefinitions($this->positionFields);

        $this->penaltyFields = [
            new StringField('id', false),
            new StringField('season_id', false),
            new StringField('team_id', false),
            new StringField('reason', false),
            new IntegerField('points', false),
            new DateTimeField('created_at', false)
        ];

        $this->penaltyHydrator = new Hydrator($this->penaltyFields);

        $this->flattenedPenaltyFields = $this->flattenFieldDefinitions($this->penaltyFields);
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

    /**
     * @param string $seasonId
     * @return array|null
     */
    public function findRanking(string $seasonId): ?array
    {
        $result = $this->gateway->fetch(
            $this->getTableName(),
            $this->flattenedFieldDefinitions,
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
            $this->flattenedPositionFields,
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
            $this->flattenedPenaltyFields,
            [],
            [new EqualityFilter($this->getPenaltyField('season_id'), Filter::MODE_INCLUDE, [$seasonId])],
            [new Sorting($this->getPenaltyField('created_at'), Sorting::DIRECTION_ASCENDING)]
        );

        return $this->penaltyHydrator->hydrateMany($result);
    }

    private function getPenaltyField(string $name): Field
    {
        foreach ($this->penaltyFields as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        throw new RuntimeException(sprintf('Unknown field "%s" for table "ranking_penalties"', $name));
    }

    private function getPositionField(string $name): Field
    {
        foreach ($this->positionFields as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        throw new RuntimeException(sprintf('Unknown field "%s" for table "ranking_positions"', $name));
    }
}
