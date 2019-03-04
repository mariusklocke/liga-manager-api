<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\EnumType;
use HexagonalPlayground\Domain\User;

class UserRoleType extends EnumType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'values' => [
                User::ROLE_ADMIN => [
                    'value' => User::ROLE_ADMIN
                ],
                User::ROLE_TEAM_MANAGER => [
                    'value' => User::ROLE_TEAM_MANAGER
                ]
            ]
        ];
        parent::__construct($config);
    }
}