<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;

class UserType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::string()
                    ],
                    'email' => [
                        'type' => Type::string()
                    ],
                    'teams' => [
                        'type' => Type::listOf(TeamType::getInstance()),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var TeamRepository $repo */
                            $repo = $context->getContainer()->get(TeamRepository::class);

                            return $repo->findTeamsByUserId($root['id']);
                        }
                    ],
                    'role' => [
                        'type' => UserRoleType::getInstance()
                    ],
                    'first_name' => [
                        'type' => Type::string()
                    ],
                    'last_name' => [
                        'type' => Type::string()
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}