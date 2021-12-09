<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;

class TeamRepository extends AbstractRepository
{
    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'name' => Hydrator::TYPE_STRING,
            'created_at' => Hydrator::TYPE_DATETIME,
            'contact' => function (array $row): ?array {
                $contact = [
                    'email' => $row['contact_email'],
                    'first_name' => $row['contact_first_name'],
                    'last_name' => $row['contact_last_name'],
                    'phone' => $row['contact_phone']
                ];

                foreach ($contact as $value) {
                    if ($value !== null) {
                        return $contact;
                    }
                }

                return null;
            }
        ];
    }

    /**
     * @param iterable|Filter[] $filters
     * @return array
     */
    public function findMany(iterable $filters = []): array
    {
        return $this->hydrateMany($this->gateway->fetch(
            'teams',
            [],
            $filters
        ));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array
    {
        return $this->hydrateOne($this->gateway->fetch(
            'teams',
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
        if (empty($seasonIds)) {
            return [];
        }

        $result = $this->gateway->fetch(
            'teams',
            ['seasons_teams_link' => ['id = team_id']],
            [new EqualityFilter('season_id', Filter::MODE_INCLUDE, $seasonIds)]
        );

        return $this->hydrateMany($result, 'season_id');
    }

    /**
     * @param array $userIds
     * @return array
     */
    public function findByUserIds(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $result = $this->gateway->fetch(
            'teams',
            ['users_teams_link' => ['id = team_id']],
            [new EqualityFilter('user_id', Filter::MODE_INCLUDE, $userIds)]
        );

        return $this->hydrateMany($result, 'user_id');
    }
}
