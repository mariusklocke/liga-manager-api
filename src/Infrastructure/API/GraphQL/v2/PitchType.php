<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\QueryTypeInterface;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Criteria\PaginationType;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\PitchRepository;

class PitchType extends ObjectType implements QueryTypeInterface
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function () {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'label' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'location' => [
                        'type' => TypeRegistry::get(GeoLocationType::class)
                    ],
                    'contact' => [
                        'type' => TypeRegistry::get(ContactType::class)
                    ],
                    'matches' => [
                        'type' => Type::listOf(TypeRegistry::get(MatchType::class)),
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var BufferedMatchLoader $loader */
                            $loader = $context->getContainer()->get(BufferedMatchLoader::class);
                            $loader->addPitch($root['id']);

                            return new Deferred(function() use ($loader, $converter, $root) {
                                return $converter->convert($loader->getByPitch($root['id']));
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
            'pitch' => [
                'type' => TypeRegistry::get(static::class),
                'args' => [
                    'id' => Type::nonNull(Type::string())
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var PitchRepository $repo */
                    $repo = $context->getContainer()->get(PitchRepository::class);
                    /** @var FieldNameConverter $converter */
                    $converter = $context->getContainer()->get(FieldNameConverter::class);

                    $pitch = $repo->findById($args['id']);

                    if ($pitch === null) {
                        return null;
                    }

                    return $converter->convert($pitch);
                }
            ],
            'pitchList' => [
                'type' => Type::listOf(TypeRegistry::get(static::class)),
                'args' => [
                    'pagination' => TypeRegistry::get(PaginationType::class)
                ],
                'resolve' => function ($root, array $args, AppContext $context) {
                    /** @var PitchRepository $repo */
                    $repo = $context->getContainer()->get(PitchRepository::class);
                    /** @var FieldNameConverter $converter */
                    $converter = $context->getContainer()->get(FieldNameConverter::class);

                    $pagination = null;
                    if (isset($args['pagination'])) {
                        $pagination = new Pagination($args['pagination']['limit'], $args['pagination']['offset']);
                    }

                    return $converter->convert($repo->findMany([], [], $pagination));
                }
            ]
        ];
    }
}
