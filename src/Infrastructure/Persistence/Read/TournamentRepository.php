<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class TournamentRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'tournaments';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            new StringField('id', false),
            new StringField('name', false),
            new IntegerField('rounds', false)
        ];
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array
    {
        return $this->hydrator->hydrateOne($this->gateway->fetch(
            $this->getTableName(),
            [],
            [new EqualityFilter('id', Filter::MODE_INCLUDE, [$id])]
        ));
    }
}
