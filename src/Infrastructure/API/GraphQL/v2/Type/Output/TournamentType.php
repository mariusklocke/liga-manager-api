<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchDayLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryTypeInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\FieldNameConverter;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\Filter\TournamentFilter;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\PaginationType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\PatternFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\TournamentRepository;
use Iterator;

class TournamentType extends ObjectType implements QueryTypeInterface
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
                    'matchDays' => [
                        'type' => Type::listOf(TypeRegistry::get(MatchDayType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var BufferedMatchDayLoader $loader */
                            $loader = $context->getContainer()->get(BufferedMatchDayLoader::class);
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);

                            $loader->addTournament($root['id']);
                            return new Deferred(function() use ($root, $loader, $converter) {
                                return $converter->convert($loader->getByTournament($root['id']));
                            });
                        }
                    ]
                ];
            }
        ]);
    }

    public function getQueries(): array
    {
        return [
            'tournament' => [
                'type' => TypeRegistry::get(static::class),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var TournamentRepository $repo */
                    $repo = $context->getContainer()->get(TournamentRepository::class);
                    /** @var FieldNameConverter $converter */
                    $converter = $context->getContainer()->get(FieldNameConverter::class);

                    $tournament = $repo->findById($args['id']);

                    return $tournament !== null ? $converter->convert($tournament) : null;
                }
            ],
            'tournamentList' => [
                'type' => Type::listOf(TypeRegistry::get(static::class)),
                'args' => [
                    'filter' => TypeRegistry::get(TournamentFilter::class),
                    'pagination' => TypeRegistry::get(PaginationType::class)
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var TournamentRepository $repo */
                    $repo = $context->getContainer()->get(TournamentRepository::class);
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
        if (isset($values['namePattern'])) {
            yield new PatternFilter('name', Filter::MODE_INCLUDE, $values['namePattern']);
        }
    }
}
