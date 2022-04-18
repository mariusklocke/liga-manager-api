<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedMatchLoader;

class MatchDayType extends ObjectType
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
                    'competition' => [
                        'type' => Type::nonNull(TypeRegistry::get(CompetitionType::class)),
                        'resolve' => function ($root, array $args, AppContext $context) {
                            return null;
                        }
                    ]
                ];
            }
        ]);
    }
}
