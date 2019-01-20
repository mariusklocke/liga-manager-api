<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use Psr\Container\ContainerInterface;

class SeasonType extends ObjectType
{
    use SingletonTrait;

    const NAME = 'Season';

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::string()
                    ],
                    'name' => [
                        'type' => Type::string()
                    ],
                    'state' => [
                        'type' => Type::string()
                    ],
                    'match_day_count' => [
                        'type' => Type::int()
                    ],
                    'team_count' => [
                        'type' => Type::int()
                    ],
                    'teams' => [
                        'type' => Type::listOf(TeamType::getInstance()),
                        'resolve' => function ($root, $args, ContainerInterface $container) {
                            /** @var TeamRepository $repo */
                            $repo = $container->get(TeamRepository::class);

                            return $repo->findTeamsBySeasonId($root['id']);
                        }
                    ],
                    'match_days' => [
                        'type' => Type::listOf(MatchDayType::getInstance()),
                        'resolve' => function ($root, $args, ContainerInterface $container) {
                            /** @var SeasonRepository $repo */
                            $repo = $container->get(SeasonRepository::class);

                            return $repo->findMatchDays($root['id']);
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
