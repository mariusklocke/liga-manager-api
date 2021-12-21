<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\RankingRepository;

class RankingType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'updated_at' => [
                        'type' => Type::string()
                    ],
                    'positions' => [
                        'type' => Type::listOf(RankingPositionType::getInstance()),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var RankingRepository $repository */
                            $repository = $context->getContainer()->get(RankingRepository::class);

                            return new Deferred(function () use ($repository, $root) {
                                return $repository->findRankingPositions($root['season_id']);
                            });
                        }
                    ],
                    'penalties' => [
                        'type' => Type::listOf(RankingPenaltyType::getInstance()),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var RankingRepository $repository */
                            $repository = $context->getContainer()->get(RankingRepository::class);

                            return new Deferred(function () use ($repository, $root) {
                                return $repository->findRankingPenalties($root['season_id']);
                            });
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
