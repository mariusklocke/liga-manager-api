<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
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
                    ]
                ];
            }
        ]);
    }
}
