<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchDayLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;

class SeasonType extends ObjectType
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
                    'name' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'state' => [
                        'type' => Type::nonNull(SeasonStateType::getInstance())
                    ],
                    'match_day_count' => [
                        'type' => Type::nonNull(Type::int())
                    ],
                    'team_count' => [
                        'type' => Type::nonNull(Type::int())
                    ],
                    'teams' => [
                        'type' => Type::listOf(TeamType::getInstance()),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var BufferedTeamLoader $loader */
                            $loader = $context->getContainer()->get(BufferedTeamLoader::class);
                            $loader->addSeason($root['id']);
                            return new Deferred(function () use ($loader, $root) {
                                return $loader->getBySeason($root['id']);
                            });
                        }
                    ],
                    'match_days' => [
                        'type' => Type::listOf(MatchDayType::getInstance()),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var BufferedMatchDayLoader $loader */
                            $loader = $context->getContainer()->get(BufferedMatchDayLoader::class);
                            $loader->addSeason($root['id']);
                            return new Deferred(function () use ($loader, $root) {
                                return $loader->getBySeason($root['id']);
                            });
                        }
                    ],
                    'ranking' => [
                        'type' => RankingType::getInstance(),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var SeasonRepository $repo */
                            $repo = $context->getContainer()->get(SeasonRepository::class);

                            try {
                                return $repo->findRanking($root['id']);
                            } catch (NotFoundException $e) {
                                return null;
                            }
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
