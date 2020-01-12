<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\UserRepository;

class UserType extends ObjectType implements QueryTypeInterface
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'email' => [
                        'type' => Type::nonNull(Type::string())
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
                        'type' => Type::nonNull(UserRoleType::getInstance())
                    ],
                    'first_name' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'last_name' => [
                        'type' => Type::nonNull(Type::string())
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }

    public function getQueries(): array
    {
        return [
            'authenticatedUser' => [
                'type' => static::getInstance(),
                'resolve' => function ($root, $args, AppContext $context) {
                    $user = $context->requireAuthContext($context->getRequest())->getUser();

                    return $user->getPublicProperties();
                }
            ],
            'allUsers' => [
                'type' => Type::listOf(static::getInstance()),
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var UserRepository $repo */
                    $repo = $context->getContainer()->get(UserRepository::class);
                    $user = $context->requireAuthContext($context->getRequest())->getUser();
                    IsAdmin::check($user);

                    return $repo->findAllUsers();
                }
            ]
        ];
    }
}