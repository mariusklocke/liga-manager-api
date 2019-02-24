<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;

class RankingPositionType extends ObjectType
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
                    'team' => [
                        'type' => TeamType::getInstance(),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var BufferedTeamLoader $loader */
                            $loader = $context->getContainer()->get(BufferedTeamLoader::class);
                            $loader->addTeam($root['team_id']);
                            return new Deferred(function () use ($loader, $root) {
                                return $loader->getByTeam($root['team_id']);
                            });
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