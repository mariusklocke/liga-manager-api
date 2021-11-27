<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class UserRepository extends AbstractRepository
{
    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'email' => Hydrator::TYPE_STRING,
            'last_password_change' => Hydrator::TYPE_DATETIME,
            'role' => Hydrator::TYPE_STRING,
            'first_name' => Hydrator::TYPE_STRING,
            'last_name' => Hydrator::TYPE_STRING
        ];
    }

    public function findAllUsers(): array
    {
        return $this->hydrateMany($this->getDb()->fetchAll('SELECT * FROM users'));
    }
}
