<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use Psr\Container\ContainerInterface;

class RankingPositionType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'team' => [
                        'type' => TeamType::getInstance(),
                        'resolve' => function (array $root, $args, ContainerInterface $container) {
                            /** @var TeamRepository $repo */
                            $repo = $container->get(TeamRepository::class);

                            return $repo->findTeamById($root['team_id']);
                        }
                    ],
                    'sort_index' => [
                        'type' => Type::int(),
                    ],
                    'number' => [
                        'type' => Type::int()
                    ],
                    'matches' => [
                        'type' => Type::int()
                    ],
                    'wins' => [
                        'type' => Type::int()
                    ],
                    'draws' => [
                        'type' => Type::int()
                    ],
                    'losses' => [
                        'type' => Type::int()
                    ],
                    'scored_goals' => [
                        'type' => Type::int()
                    ],
                    'conceded_goals' => [
                        'type' => Type::int()
                    ],
                    'points' => [
                        'type' => Type::int()
                    ],
                ];
            }
        ];
        parent::__construct($config);
    }
}