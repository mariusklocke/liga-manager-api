<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\FieldNameConverter;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;

class RankingPenaltyType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'team' => [
                        'type' => Type::nonNull(TypeRegistry::get(TeamType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var BufferedTeamLoader $loader */
                            $loader = $context->getContainer()->get(BufferedTeamLoader::class);
                            $loader->addTeam($root['teamId']);
                            return new Deferred(function () use ($loader, $converter, $root) {
                                return $converter->convert($loader->getByTeam($root['teamId']));
                            });
                        }
                    ],
                    'reason' => [
                        'type' => Type::nonNull(Type::string()),
                    ],
                    'createdAt' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'points' => [
                        'type' => Type::nonNull(Type::int())
                    ]
                ];
            }
        ]);
    }
}
