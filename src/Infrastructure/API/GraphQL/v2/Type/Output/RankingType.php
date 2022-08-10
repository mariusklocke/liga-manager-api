<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Output;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedSeasonLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\FieldNameConverter;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Scalar\DateTimeType;
use HexagonalPlayground\Infrastructure\API\GraphQL\v2\TypeRegistry;
use HexagonalPlayground\Infrastructure\Persistence\Read\RankingRepository;

class RankingType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function() {
                return [
                    'updatedAt' => [
                        'type' => TypeRegistry::get(DateTimeType::class)
                    ],
                    'positions' => [
                        'type' => Type::listOf(TypeRegistry::get(RankingPositionType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var RankingRepository $repository */
                            $repository = $context->getContainer()->get(RankingRepository::class);

                            return new Deferred(function () use ($repository, $converter, $root) {
                                return $converter->convert($repository->findRankingPositions($root['seasonId']));
                            });
                        }
                    ],
                    'penalties' => [
                        'type' => Type::listOf(TypeRegistry::get(RankingPenaltyType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);
                            /** @var RankingRepository $repository */
                            $repository = $context->getContainer()->get(RankingRepository::class);

                            return new Deferred(function () use ($repository, $converter, $root) {
                                return $converter->convert($repository->findRankingPenalties($root['seasonId']));
                            });
                        }
                    ],
                    'season' => [
                        'type' => Type::nonNull(TypeRegistry::get(SeasonType::class)),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var BufferedSeasonLoader $loader */
                            $loader = $context->getContainer()->get(BufferedSeasonLoader::class);
                            $loader->addSeasonId($root['seasonId']);

                            /** @var FieldNameConverter $converter */
                            $converter = $context->getContainer()->get(FieldNameConverter::class);

                            return new Deferred(function () use ($loader, $converter, $root) {
                                return $converter->convert($loader->getBySeason($root['seasonId']));
                            });
                        }
                    ]
                ];
            }
        ]);
    }
}
