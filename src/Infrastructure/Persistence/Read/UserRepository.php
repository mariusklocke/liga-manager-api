<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class UserRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'users';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            new StringField('id', false),
            new StringField('email', false),
            new DateTimeField('last_password_change', true),
            new StringField('role', false),
            new StringField('first_name', false),
            new StringField('last_name', false)
        ];
    }

    public function findByTeamIds(array $teamIds): array
    {
        $fields = $this->flattenedFieldDefinitions;
        $fields['team_id'] = new StringField('team_id', false);

        $result = $this->gateway->fetch(
            $this->getTableName(),
            $fields,
            ['users_teams_link' => 'id = user_id'],
            [new EqualityFilter(
                'team_id',
                Filter::MODE_INCLUDE,
                $teamIds
            )]
        );

        return $this->hydrator->hydrateMany($result, 'team_id');
    }
}
