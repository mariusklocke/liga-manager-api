<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use Psr\Container\ContainerInterface;

class UserType extends ObjectType
{
    use SingletonTrait;

    const NAME = 'User';

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
                        'resolve' => function ($root, $args, ContainerInterface $container) {
                            /** @var TeamRepository $repo */
                            $repo = $container->get(TeamRepository::class);

                            return $repo->findTeamsByUserId($root['id']);
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}