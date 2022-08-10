<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedSeasonLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTournamentLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryTypeInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\FieldNameConverter;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\Filter\MatchDayFilterType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\PaginationType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar\DateType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\RangeFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchDayRepository;
use Iterator;

class MatchDayType extends ObjectType implements QueryTypeInterface
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function () {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'number' => [
                        'type' => Type::nonNull(Type::int())
                    ],
                    'startDate' => [
                        'type' => Type::nonNull(TypeRegistry::get(DateType::class))
                    ],
                    'endDate' => [
                        'type' => Type::nonNull(TypeRegistry::get(DateType::class))
                    ],
                    'matches' => [
                        'type' => Type::listOf(TypeRegistry::get(MatchType::class)),
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var BufferedMatchLoader $loader */
                            $loader = $context->getContainer()->get(BufferedMatchLoader::class);
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);

                            $loader->addMatchDay($root['id']);

                            return new Deferred(function() use ($loader, $converter, $root) {
                                return $converter->convert($loader->getByMatchDay($root['id']));
                            });
                        }
                    ],
                    'season' => [
                        'type' => TypeRegistry::get(SeasonType::class),
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var BufferedSeasonLoader $loader */
                            $loader = $context->getContainer()->get(BufferedSeasonLoader::class);
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);

                            $loader->addSeasonId($root['id']);

                            return new Deferred(function() use ($loader, $converter, $root) {
                                return $converter->convert($loader->getBySeason($root['id']));
                            });
                        }
                    ],
                    'tournament' => [
                        'type' => TypeRegistry::get(TournamentType::class),
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var BufferedTournamentLoader $loader */
                            $loader = $context->getContainer()->get(BufferedTournamentLoader::class);
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);

                            $loader->addTournamentId($root['id']);

                            return new Deferred(function() use ($loader, $converter, $root) {
                                return $converter->convert($loader->getByTournament($root['id']));
                            });
                        }
                    ],
                ];
            }
        ]);
    }

    public function getQueries(): array
    {
        return [
            'matchDay' => [
                'type' => TypeRegistry::get(static::class),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var MatchDayRepository $repo */
                    $repo = $context->getContainer()->get(MatchDayRepository::class);
                    /** @var FieldNameConverter $converter */
                    $converter = $context->getContainer()->get(FieldNameConverter::class);

                    $match = $repo->findById($args['id']);

                    return $match !== null ? $converter->convert($match) : null;
                }
            ],
            'matchDayList' => [
                'type' => Type::listOf(TypeRegistry::get(static::class)),
                'args' => [
                    'filter' => TypeRegistry::get(MatchDayFilterType::class),
                    'pagination' => TypeRegistry::get(PaginationType::class)
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var MatchDayRepository $repo */
                    $repo = $context->getContainer()->get(MatchDayRepository::class);
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
        if (isset($values['startsAfter']) || isset($values['startsBefore'])) {
            yield new RangeFilter(
                'start_date',
                Filter::MODE_INCLUDE,
                $values['startsAfter'] ?? null,
                $values['startsBefore'] ?? null
            );
        }

        if (isset($values['endsAfter']) || isset($values['endsBefore'])) {
            yield new RangeFilter(
                'end_date',
                Filter::MODE_INCLUDE,
                $values['endsAfter'] ?? null,
                $values['endsBefore'] ?? null
            );
        }
    }
}
