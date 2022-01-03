<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\EmbeddedObjectField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class TeamRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'teams';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            new StringField('id', false),
            new StringField('name', false),
            new DateTimeField('created_at', false),
            new EmbeddedObjectField('contact', true, [
                new StringField('email', false),
                new StringField('first_name', false),
                new StringField('last_name', false),
                new StringField('phone', false)
            ])
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

    /**
     * @param array $seasonIds
     * @return array
     */
    public function findBySeasonIds(array $seasonIds): array
    {
        $result = $this->gateway->fetch(
            $this->getTableName(),
            ['seasons_teams_link' => 'id = team_id'],
            [new EqualityFilter('season_id', Filter::MODE_INCLUDE, $seasonIds)]
        );

        return $this->hydrator->hydrateMany($result, 'season_id');
    }

    /**
     * @param array $userIds
     * @return array
     */
    public function findByUserIds(array $userIds): array
    {
        $result = $this->gateway->fetch(
            $this->getTableName(),
            ['users_teams_link' => 'id = team_id'],
            [new EqualityFilter('user_id', Filter::MODE_INCLUDE, $userIds)]
        );

        return $this->hydrator->hydrateMany($result, 'user_id');
    }
}
