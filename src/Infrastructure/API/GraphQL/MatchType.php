<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use Psr\Container\ContainerInterface;

class MatchType extends ObjectType
{
    use SingletonTrait;

    const NAME = 'Match';

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::string()
                    ],
                    'home_team' => [
                        'type' => TeamType::getInstance(),
                        'resolve' => function ($root, $args, ContainerInterface $container) {
                            /** @var TeamRepository $repo */
                            $repo = $container->get(TeamRepository::class);

                            return $repo->findTeamById($root['home_team_id']);
                        }
                    ],
                    'guest_team' => [
                        'type' => TeamType::getInstance(),
                        'resolve' => function ($root, $args, ContainerInterface $container) {
                            /** @var TeamRepository $repo */
                            $repo = $container->get(TeamRepository::class);

                            return $repo->findTeamById($root['guest_team_id']);
                        }
                    ],
                    'kickoff' => [
                        'type' => Type::string()
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}