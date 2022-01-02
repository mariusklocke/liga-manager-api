<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

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
}
