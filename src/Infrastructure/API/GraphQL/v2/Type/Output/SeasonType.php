<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchDayLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryTypeInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\FieldNameConverter;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\Filter\SeasonFilterType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\PaginationType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\RankingRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;
use Iterator;

class SeasonType extends ObjectType implements QueryTypeInterface
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function () {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'name' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'state' => [
                        'type' => Type::nonNull(TypeRegistry::get(SeasonStateType::class))
                    ],
                    'matchDayCount' => [
                        'type' => Type::nonNull(Type::int())
                    ],
                    'teamCount' => [
                        'type' => Type::nonNull(Type::int())
                    ],
                    'teams' => [
                        'type' => Type::listOf(TypeRegistry::get(TeamType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var BufferedTeamLoader $loader */
                            $loader = $context->getContainer()->get(BufferedTeamLoader::class);
                            $loader->addSeason($root['id']);
                            return new Deferred(function() use ($root, $loader, $converter) {
                                return $converter->convert($loader->getBySeason($root['id']));
                            });
                        }
                    ],
                    'matchDays' => [
                        'type' => Type::listOf(TypeRegistry::get(MatchDayType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var BufferedMatchDayLoader $loader */
                            $loader = $context->getContainer()->get(BufferedMatchDayLoader::class);
                            $loader->addSeason($root['id']);
                            return new Deferred(function() use ($root, $loader, $converter) {
                                return $converter->convert($loader->getBySeason($root['id']));
                            });
                        }
                    ],
                    'ranking' => [
                        'type' => TypeRegistry::get(RankingType::class),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var RankingRepository $repo */
                            $repo = $context->getContainer()->get(RankingRepository::class);

                            $ranking = $repo->findRanking($root['id']);

                            return $ranking !== null ? $converter->convert($ranking) : null;
                        }
                    ]
                ];
            }
        ]);
    }

    public function getQueries(): array
    {
        return [
            'season' => [
                'type' => TypeRegistry::get(static::class),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var SeasonRepository $repo */
                    $repo = $context->getContainer()->get(SeasonRepository::class);
                    /** @var FieldNameConverter $converter */
                    $converter = $context->getContainer()->get(FieldNameConverter::class);

                    $tournament = $repo->findById($args['id']);

                    return $tournament !== null ? $converter->convert($tournament) : null;
                }
            ],
            'seasonList' => [
                'type' => Type::listOf(TypeRegistry::get(static::class)),
                'args' => [
                    'filter' => TypeRegistry::get(SeasonFilterType::class),
                    'pagination' => TypeRegistry::get(PaginationType::class)
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var SeasonRepository $repo */
                    $repo = $context->getContainer()->get(SeasonRepository::class);
                    /** @var FieldNameConverter $converter */
                    $converter = $context->getContainer()->get(FieldNameConverter::class);

                    $filters = [];
                    if (isset($args['filter'])) {
                        $filters = $this->buildFilters($args['filter']);
                    }

                    $pagination = null;
                    if (isset($args['pagination'])) {
                        $pagination = new Pagination($args['pagination']['limit'], $args['pagination']['offset']);
                    }

                    return $converter->convert($repo->findMany($filters, [], $pagination));
                }
            ]
        ];
    }

    private function buildFilters(array $values): Iterator
    {
        if (isset($values['states'])) {
            yield new EqualityFilter('state', Filter::MODE_INCLUDE, $values['states']);
        }
    }
}
