<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;

class RankingPenaltyType extends ObjectType
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
                    'team' => [
                        'type' => Type::nonNull(TeamType::getInstance()),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var BufferedTeamLoader $loader */
                            $loader = $context->getContainer()->get(BufferedTeamLoader::class);
                            $loader->addTeam($root['team_id']);
                            return new Deferred(function () use ($loader, $root) {
                                return $loader->getByTeam($root['team_id']);
                            });
                        }
                    ],
                    'reason' => [
                        'type' => Type::nonNull(Type::string()),
                    ],
                    'created_at' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'points' => [
                        'type' => Type::nonNull(Type::int())
                    ],
                ];
            }
        ];
        parent::__construct($config);
    }
}