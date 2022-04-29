<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Enum;

use GraphQL\Type\Definition\EnumType;
use HexagonalPlayground\Domain\User;

class UserRoleType extends EnumType
{
    public function __construct()
    {
        parent::__construct([
            'values' => [
                User::ROLE_ADMIN => [
                    'value' => User::ROLE_ADMIN
                ],
                User::ROLE_TEAM_MANAGER => [
                    'value' => User::ROLE_TEAM_MANAGER
                ]
            ]
        ]);
    }
}
