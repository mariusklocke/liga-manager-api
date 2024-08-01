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
            'values' => []
        ];

        foreach (User::getRoles() as $role) {
            $config['values'][$role] = ['value' => $role];
        }

        parent::__construct($config);
    }
}
