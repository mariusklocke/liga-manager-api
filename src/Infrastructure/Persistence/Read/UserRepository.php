<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class UserRepository extends AbstractRepository
{
    public function findAllUsers(): array
    {
        return array_map([$this, 'hydrate'], $this->getDb()->fetchAll('SELECT * FROM users'));
    }

    protected function hydrate(array $row): array
    {
        return [
            'id' => $this->hydrator->string($row['id']),
            'email' => $this->hydrator->string($row['email']),
            'last_password_change' => $this->hydrator->dateTime($row['last_password_change']),
            'role' => $this->hydrator->string($row['role']),
            'first_name' => $this->hydrator->string($row['first_name']),
            'last_name' => $this->hydrator->string($row['last_name'])
        ];
    }
}
