<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class UserRepository extends AbstractRepository
{
    public function findAllUsers(): array
    {
        return $this->getDb()->fetchAll(
            'SELECT id, email, last_password_change, role, first_name, last_name FROM users'
        );
    }
}