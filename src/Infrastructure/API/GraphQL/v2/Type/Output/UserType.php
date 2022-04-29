<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryTypeInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\FieldNameConverter;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Enum\UserRoleType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\Filter\UserFilterType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input\PaginationType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\UserRepository;
use Iterator;

class UserType extends ObjectType implements QueryTypeInterface
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function () {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'email' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'teams' => [
                        'type' => Type::listOf(TypeRegistry::get(TeamType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var BufferedTeamLoader $loader */
                            $loader = $context->getContainer()->get(BufferedTeamLoader::class);
                            $loader->addUser($root['id']);

                            return new Deferred(function () use ($loader, $converter, $root) {
                                return $converter->convert($loader->getByUser($root['id']));
                            });
                        }
                    ],
                    'role' => [
                        'type' => Type::nonNull(TypeRegistry::get(UserRoleType::class))
                    ],
                    'firstName' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'lastName' => [
                        'type' => Type::nonNull(Type::string())
                    ]
                ];
            }
        ]);
    }

    public function getQueries(): array
    {
        return [
            'user' => [
                'type' => TypeRegistry::get(static::class),
                'args' => [
                    'id' => Type::string()
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    $authContext = (new AuthReader())->requireAuthContext($context->getRequest());
                    /** @var UserRepository $repo */
                    $repo = $context->getContainer()->get(UserRepository::class);
                    /** @var FieldNameConverter $converter */
                    $converter = $context->getContainer()->get(FieldNameConverter::class);

                    if (!isset($args['id']) || $args['id'] === $authContext->getUser()->getId()) {
                        return $converter->convert($authContext->getUser()->getPublicProperties());
                    }

                    // Fetching other user's data requires admin role
                    (new IsAdmin($authContext->getUser()))->check();

                    $user = $repo->findById($args['id']);

                    return $user !== null ? $converter->convert($user): null;
                }
            ],
            'userList' => [
                'type' => Type::listOf(TypeRegistry::get(static::class)),
                'args' => [
                    'filter' => TypeRegistry::get(UserFilterType::class),
                    'pagination' => TypeRegistry::get(PaginationType::class)
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    $authContext = (new AuthReader())->requireAuthContext($context->getRequest());
                    // Listing user data requires admin role
                    (new IsAdmin($authContext->getUser()))->check();

                    /** @var UserRepository $repo */
                    $repo = $context->getContainer()->get(UserRepository::class);
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
        if (isset($values['roles'])) {
            yield new EqualityFilter('role', Filter::MODE_INCLUDE, $values['roles']);
        }
    }
}
