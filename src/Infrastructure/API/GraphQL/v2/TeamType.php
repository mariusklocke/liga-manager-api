<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedUserLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryTypeInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Criteria\Filter\TeamFilterType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Criteria\PaginationType;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\PatternFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use Iterator;

class TeamType extends ObjectType implements QueryTypeInterface
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
                    'createdAt' => [
                        'type' => Type::nonNull(TypeRegistry::get(DateTimeType::class))
                    ],
                    'contact' => [
                        'type' => TypeRegistry::get(ContactType::class)
                    ],
                    'users' => [
                        'type' => Type::listOf(TypeRegistry::get(UserType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            $authContext = (new AuthReader())->requireAuthContext($context->getRequest());
                            // Listing user data requires admin role
                            (new IsAdmin($authContext->getUser()))->check();

                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var BufferedUserLoader $loader */
                            $loader = $context->getContainer()->get(BufferedUserLoader::class);
                            $loader->addTeam($root['id']);

                            return new Deferred(function () use ($root, $loader, $converter) {
                                return $converter->convert($loader->getByTeam($root['id']));
                            });
                        }
                    ],
                    'homeMatches' => [
                        'type' => Type::listOf(TypeRegistry::get(MatchType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var BufferedMatchLoader $loader */
                            $loader = $context->getContainer()->get(BufferedMatchLoader::class);
                            $loader->addHomeTeam($root['id']);

                            return new Deferred(function () use ($root, $loader, $converter) {
                                return $converter->convert($loader->getByHomeTeam($root['id']));
                            });
                        }
                    ],
                    'guestMatches' => [
                        'type' => Type::listOf(TypeRegistry::get(MatchType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var BufferedMatchLoader $loader */
                            $loader = $context->getContainer()->get(BufferedMatchLoader::class);
                            $loader->addGuestTeam($root['id']);

                            return new Deferred(function () use ($root, $loader, $converter) {
                                return $converter->convert($loader->getByGuestTeam($root['id']));
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
            'team' => [
                'type' => TypeRegistry::get(static::class),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var TeamRepository $repo */
                    $repo = $context->getContainer()->get(TeamRepository::class);
                    /** @var FieldNameConverter $converter */
                    $converter = $context->getContainer()->get(FieldNameConverter::class);

                    $team = $repo->findById($args['id']);

                    return $team !== null ? $converter->convert($team) : null;
                }
            ],
            'teamList' => [
                'type' => Type::listOf(TypeRegistry::get(static::class)),
                'args' => [
                    'filter' => TypeRegistry::get(TeamFilterType::class),
                    'pagination' => TypeRegistry::get(PaginationType::class)
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var TeamRepository $repo */
                    $repo = $context->getContainer()->get(TeamRepository::class);
                    /** @var FieldNameConverter $converter */
                    $converter = $context->getContainer()->get(FieldNameConverter::class);

                    $filters = [];
                    if (isset($args['filter'])) {
                        $filters = $this->buildFilters($args['filter']);
                    }

                    $sortings = [new Sorting('created_at', Sorting::DIRECTION_ASCENDING)];

                    $pagination = null;
                    if (isset($args['pagination'])) {
                        $pagination = new Pagination($args['pagination']['limit'], $args['pagination']['offset']);
                    }

                    return $converter->convert($repo->findMany($filters, $sortings, $pagination));
                }
            ]
        ];
    }

    /**
     * @param array $values
     * @return Iterator|Filter[]
     */
    private function buildFilters(array $values): Iterator
    {
        if (isset($values['namePattern'])) {
            yield new PatternFilter('name', Filter::MODE_INCLUDE, $values['namePattern']);
        }
    }
}
