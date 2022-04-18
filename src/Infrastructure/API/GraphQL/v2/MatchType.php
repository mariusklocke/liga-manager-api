<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchDayLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryTypeInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Criteria\Filter\MatchFilterType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Criteria\PaginationType;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\RangeFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;
use Iterator;

class MatchType extends ObjectType implements QueryTypeInterface
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function () {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'matchDay' => [
                        'type' => Type::nonNull(TypeRegistry::get(MatchDayType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var BufferedMatchDayLoader $loader */
                            $loader = $context->getContainer()->get(BufferedMatchDayLoader::class);
                            $loader->addMatchDay($root['matchDayId']);

                            return new Deferred(function() use ($loader, $converter, $root) {
                                return $converter->convert($loader->getByMatchDay($root['matchDayId']));
                            });
                        }
                    ],
                    'homeTeam' => [
                        'type' => Type::nonNull(TypeRegistry::get(TeamType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            return $this->resolveTeam($root['homeTeamId'], $context);
                        }
                    ],
                    'guestTeam' => [
                        'type' => Type::nonNull(TypeRegistry::get(TeamType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            return $this->resolveTeam($root['guestTeamId'], $context);
                        }
                    ],
                    'kickoff' => [
                        'type' => TypeRegistry::get(DateTimeType::class)
                    ],
                    'result' => [
                        'type' => TypeRegistry::get(MatchResultType::class)
                    ],
                    'cancellation' => [
                        'type' => TypeRegistry::get(MatchCancellationType::class)
                    ],
                    'pitch' => [
                        'type' => TypeRegistry::get(PitchType::class)
                    ]
                ];
            }
        ]);
    }

    public function getQueries(): array
    {
        return [
            'match' => [
                'type' => TypeRegistry::get(static::class),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var MatchRepository $repo */
                    $repo = $context->getContainer()->get(MatchRepository::class);
                    /** @var FieldNameConverter $converter */
                    $converter = $context->getContainer()->get(FieldNameConverter::class);

                    $match = $repo->findById($args['id']);

                    return $match !== null ? $converter->convert($match) : null;
                }
            ],
            'matchList' => [
                'type' => Type::listOf(TypeRegistry::get(static::class)),
                'args' => [
                    'filter' => TypeRegistry::get(MatchFilterType::class),
                    'pagination' => TypeRegistry::get(PaginationType::class)
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var MatchRepository $repo */
                    $repo = $context->getContainer()->get(MatchRepository::class);
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

    private function resolveTeam(string $teamId, AppContext $context): Deferred
    {
        /** @var BufferedTeamLoader $loader */
        $loader = $context->getContainer()->get(BufferedTeamLoader::class);
        $loader->addTeam($teamId);

        return new Deferred(function() use ($loader, $teamId) {
            return $loader->getByTeam($teamId);
        });
    }

    private function buildFilters(array $values): Iterator
    {
        if (isset($values['kickoffAfter']) || isset($values['kickoffBefore'])) {
            yield new RangeFilter(
                'kickoff',
                Filter::MODE_INCLUDE,
                $values['kickoffAfter'] ?? null,
                $values['kickoffBefore'] ?? null
            );
        }
    }
}
